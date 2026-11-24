import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 20,
  iterations: 500,
};

export default function () {
  const res = http.get('http://host.docker.internal:8088/api/debug/instance');
  check(res, {
    'status is 200': (r) => r.status === 200,
  });
}