// اختبار الصحة: "تعديل المنتج يظهر فوراً للمستخدم" رغم Redis و ETag ونسخة القراءة.
// أي نتيجة قديمة = فشل. شغّله مع كل التحسينات مفعّلة.
//   docker run --rm -i --network host -v "$PWD/loadtest:/loadtest" grafana/k6 run /loadtest/freshness-test.js
import http from 'k6/http';
import { check } from 'k6';
import { Rate } from 'k6/metrics';

const BASE = __ENV.BASE || 'http://localhost:8000/api/v1';
const ITER = parseInt(__ENV.ITER || '30');
const fresh = new Rate('fresh_ok');
const JSON_H = { 'Content-Type': 'application/json', Accept: 'application/json' };

export const options = {
  vus: 1, iterations: 1,
  thresholds: { fresh_ok: ['rate==1'] },
};

function post(url, body, token, headers) {
  const h = Object.assign({ Accept: 'application/json' }, headers || {});
  if (token) h.Authorization = `Bearer ${token}`;
  return http.post(url, body, { headers: h });
}

export default function () {
  const admin = post(`${BASE}/auth/login`, JSON.stringify({ email: 'admin@shop.local', password: 'Admin12345' }), null, JSON_H).json('token');
  const run = Date.now();
  const cust = post(`${BASE}/auth/register`, JSON.stringify({
    name: 'Fresh', email: `fresh${run}@t.local`, password: 'Passw0rd!!', password_confirmation: 'Passw0rd!!',
  }), null, JSON_H).json('token');
  const cat = `fresh-${run}`;

  for (let i = 0; i < ITER; i++) {
    // 1) سخّن الكاش بقائمة التصنيف (فارغة) قبل الإضافة
    http.get(`${BASE}/products?category=${cat}`);

    // 2) إنشاء منتج => يجب أن يظهر في القائمة فوراً
    const created = post(`${BASE}/admin/products`, { name: `Fresh ${i}`, price: 10, category: cat, stock: 5 }, admin);
    const id = created.json('product.id');
    const list = http.get(`${BASE}/products?category=${cat}`).json('data');
    fresh.add(check(list, { 'new product in list': (l) => l.some((p) => p.id === id) }));

    // 3) تعديل السعر => يجب أن يظهر في التفاصيل وفي القائمة فوراً
    const newPrice = (20 + i).toFixed(2);
    post(`${BASE}/admin/products/${id}`, { price: newPrice }, admin);
    const d1 = http.get(`${BASE}/products/${id}`).json('price');
    fresh.add(check(d1, { 'detail shows new price': (p) => p === newPrice }));
    const l2 = http.get(`${BASE}/products?category=${cat}`).json('data');
    fresh.add(check(l2, { 'list shows new price': (l) => l.find((p) => p.id === id).price === newPrice }));

    // 4) الزبون ينشئ طلباً ثم يطلب قائمة طلباته فوراً => يجب أن يرى طلبه
    const o = post(`${BASE}/orders`, JSON.stringify({ items: [{ product_id: id, quantity: 1 }] }), cust, JSON_H);
    const oid = o.json('data.id');
    const mine = http.get(`${BASE}/orders`, { headers: { Authorization: `Bearer ${cust}`, Accept: 'application/json' } }).json('data');
    fresh.add(check(mine, { 'my order visible': (l) => l.some((x) => x.id === oid) }));

    // 5) الكمية حيّة وغير مخزّنة: تنقص بعد الطلب
    const d2 = http.get(`${BASE}/products/${id}`).json('stock');
    fresh.add(check(d2, { 'stock decreased live': (s) => s === 4 }));
  }

  // 6) حذف => 404 فوراً
  const gone = post(`${BASE}/admin/products`, { name: 'To delete', price: 1, category: cat }, admin).json('product.id');
  http.get(`${BASE}/products/${gone}`);
  http.del(`${BASE}/admin/products/${gone}`, null, { headers: { Authorization: `Bearer ${admin}`, Accept: 'application/json' } });
  fresh.add(check(http.get(`${BASE}/products/${gone}`), { 'deleted => 404': (r) => r.status === 404 }));
}
