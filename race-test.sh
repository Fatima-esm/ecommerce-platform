#!/usr/bin/env bash
# race-test.sh - concurrent orders on a product with stock = 1 (run it in Git Bash)
# Usage:   bash race-test.sh            (optional: N=20 bash race-test.sh)
API="${API:-http://127.0.0.1:8088/api/v1}"
N="${N:-10}"
H_JSON=(-H "Content-Type: application/json" -H "Accept: application/json")

# reads one field value from a JSON text on stdin (simple, no jq needed)
json_field() { grep -oE "\"$1\":\"?[^\",}]+" | head -1 | sed -E "s/\"$1\":\"?//"; }

echo "1) admin login"
ADMIN=$(curl -s -X POST "$API/auth/login" "${H_JSON[@]}" \
  -d '{"email":"admin@shop.local","password":"Admin12345"}' | json_field token)
[ -n "$ADMIN" ] || { echo "admin login failed"; exit 1; }

echo "2) new customer"
EMAIL="race$RANDOM$RANDOM@t.local"
curl -s -o /dev/null -X POST "$API/auth/register" "${H_JSON[@]}" \
  -d "{\"name\":\"Race\",\"email\":\"$EMAIL\",\"password\":\"Passw0rd!!\",\"password_confirmation\":\"Passw0rd!!\"}"
TOKEN=$(curl -s -X POST "$API/auth/login" "${H_JSON[@]}" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"Passw0rd!!\"}" | json_field token)
[ -n "$TOKEN" ] || { echo "customer login failed"; exit 1; }

echo "3) new product with stock = 1"
PID=$(curl -s -X POST "$API/admin/products" "${H_JSON[@]}" -H "Authorization: Bearer $ADMIN" \
  -d '{"name":"Race Test Item","price":10,"category":"phones","stock":1}' | json_field id)
[ -n "$PID" ] || { echo "product creation failed"; exit 1; }
echo "   product id = $PID"

TMP=$(mktemp -d)
echo "4) firing $N concurrent orders for product $PID (stock = 1)"
for i in $(seq 1 "$N"); do
  curl -s -o "$TMP/body$i.json" -w "%{http_code}" -X POST "$API/orders" "${H_JSON[@]}" \
    -H "Authorization: Bearer $TOKEN" \
    -d "{\"items\":[{\"product_id\":$PID,\"quantity\":1}]}" > "$TMP/code$i.txt" &
done
wait

echo "5) results"
OK=0
for i in $(seq 1 "$N"); do
  CODE=$(cat "$TMP/code$i.txt")
  echo "request $i -> HTTP $CODE  $(head -c 110 "$TMP/body$i.json")"
  if [ "$CODE" = "201" ] || [ "$CODE" = "200" ]; then OK=$((OK+1)); fi
done
echo "successful orders: $OK   (expected: exactly 1)"

echo "6) stock after the test"
curl -s "$API/products/$PID" -H "Accept: application/json" | grep -oE '"stock":[^,}]+'
echo "(expected: \"stock\":0  - never negative)"
rm -rf "$TMP"
