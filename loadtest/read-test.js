// اختبار حمل للقراءة (المتطلب الثالث) — يقيس p50 / p95 / p99 لكل نوع طلب.
//
// تشغيل (من مجلد المشروع):
//   docker run --rm -i --network host -v "$PWD/loadtest:/loadtest" -e LABEL=before \
//     grafana/k6 run /loadtest/read-test.js
// متغيرات: BASE, LABEL, VUS(30), DURATION(30s), USE_ETAG(true|false), PRODUCTS(200000), HOT(1000)
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter } from 'k6/metrics';

const BASE = __ENV.BASE || 'http://localhost:8000/api/v1';
const LABEL = __ENV.LABEL || 'run';
const USE_ETAG = (__ENV.USE_ETAG || 'false') === 'true';
const PRODUCTS = parseInt(__ENV.PRODUCTS || '200000');
const HOT = parseInt(__ENV.HOT || '1000');   // "الأكثر قراءة": 80% من الطلبات على أول HOT منتج
const CATEGORIES = ['phones', 'laptops', 'cameras', 'wearables', 'audio', 'tablets', 'monitors', 'accessories'];

const notModified = new Counter('responses_304');

http.setResponseCallback(http.expectedStatuses({ min: 200, max: 399 }));

export const options = {
  vus: parseInt(__ENV.VUS || '30'),
  duration: __ENV.DURATION || '30s',
  summaryTrendStats: ['avg', 'med', 'p(95)', 'p(99)', 'max'],
  thresholds: {
    http_req_failed: ['rate<0.01'],
    // تعريف المقاييس الفرعية حتى تظهر في الملخص (العتبات شكلية)
    'http_req_duration{endpoint:list}': ['max>=0'],
    'http_req_duration{endpoint:detail}': ['max>=0'],
    'http_req_duration{endpoint:search}': ['max>=0'],
    'http_req_duration{endpoint:my_orders}': ['max>=0'],
  },
};

export function setup() {
  const r = http.post(`${BASE}/auth/login`,
    JSON.stringify({ email: 'user1@perf.test', password: 'Password123!' }),
    { headers: { 'Content-Type': 'application/json', Accept: 'application/json' } });
  check(r, { 'login ok': (x) => x.status === 200 });
  return { token: r.json('token') };
}

const etags = {}; // لكل VU: url -> etag

function get(url, endpoint, token) {
  const headers = { Accept: 'application/json' };
  if (token) headers.Authorization = `Bearer ${token}`;
  if (USE_ETAG && etags[url]) headers['If-None-Match'] = etags[url];
  const res = http.get(url, { headers, tags: { endpoint } });
  if (res.status === 304) notModified.add(1);
  if (USE_ETAG && res.headers['Etag']) etags[url] = res.headers['Etag'];
  check(res, { 'status ok': (r) => r.status === 200 || r.status === 304 });
  return res;
}

function pickProductId() {
  return Math.random() < 0.8
    ? 1 + Math.floor(Math.random() * HOT)
    : 1 + Math.floor(Math.random() * PRODUCTS);
}

export default function (data) {
  const x = Math.random();
  if (x < 0.40) {
    const c = CATEGORIES[Math.floor(Math.random() * CATEGORIES.length)];
    const page = 1 + Math.floor(Math.random() * 3);
    get(`${BASE}/products?category=${c}&page=${page}`, 'list');
  } else if (x < 0.80) {
    get(`${BASE}/products/${pickProductId()}`, 'detail');
  } else if (x < 0.90) {
    const q = ['phone 1', 'laptop 2', 'camera 3', 'watch 4'][Math.floor(Math.random() * 4)];
    get(`${BASE}/products?search=${encodeURIComponent(q)}`, 'search');
  } else {
    get(`${BASE}/orders`, 'my_orders', data.token);
  }
  sleep(0.05);
}

function row(name, m) {
  if (!m) return `${name.padEnd(12)} -`;
  const v = m.values;
  return `${name.padEnd(12)} p50=${v.med.toFixed(1).padStart(8)}  p95=${v['p(95)'].toFixed(1).padStart(8)}  p99=${v['p(99)'].toFixed(1).padStart(8)}  (ms)`;
}

export function handleSummary(data) {
  const m = data.metrics;
  const lines = [
    `=== ${LABEL} ===`,
    row('ALL', m['http_req_duration']),
    row('list', m['http_req_duration{endpoint:list}']),
    row('detail', m['http_req_duration{endpoint:detail}']),
    row('search', m['http_req_duration{endpoint:search}']),
    row('my_orders', m['http_req_duration{endpoint:my_orders}']),
    `requests/s  = ${m.http_reqs.values.rate.toFixed(1)}`,
    `failed      = ${(m.http_req_failed.values.rate * 100).toFixed(2)}%`,
    `304 replies = ${m.responses_304 ? m.responses_304.values.count : 0}`,
    '',
  ].join('\n');
  return { stdout: lines, [`/results/${LABEL}.json`]: JSON.stringify(data.metrics, null, 1) };
}
