import { createServer } from 'node:http';
import { readFileSync } from 'node:fs';

const template = readFileSync(new URL('./index.html', import.meta.url), 'utf8');
const formatTime = new Intl.DateTimeFormat('ru-RU', {
  timeZone: 'Europe/Moscow',
  dateStyle: 'long',
  timeStyle: 'medium',
});

const server = createServer((request, response) => {
  const path = request.url.split('?')[0];
  if (path !== '/') {
    response.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
    response.end('Страница не найдена');
    return;
  }

  if (request.method !== 'GET' && request.method !== 'HEAD') {
    response.writeHead(405, { Allow: 'GET, HEAD' });
    response.end();
    return;
  }

  const now = new Date();
  const html = template
    .replace('{{datetime}}', now.toISOString())
    .replace('{{time}}', formatTime.format(now));

  response.writeHead(200, {
    'Content-Type': 'text/html; charset=utf-8',
    'Cache-Control': 'no-store',
  });
  response.end(request.method === 'HEAD' ? undefined : html);
});

server.listen(Number(process.env.PORT ?? 3000), '127.0.0.1', () => {
  console.log('http://127.0.0.1:' + server.address().port);
});
