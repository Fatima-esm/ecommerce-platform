import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 10,
  iterations: 5000,
};

export default function () {
  const response = http.get(
    'http://127.0.0.1:8088/api/debug/instance'
  );

  check(response, {
    'status is 200': (r) => r.status === 200,
  });
}