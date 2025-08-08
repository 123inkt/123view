const http = require('http');
const url  = require('url');

const Prism = require('prismjs');
const loadLanguages = require('prismjs/components/');
loadLanguages(['ini', 'json', 'apacheconf', 'less', 'markdown', 'php', 'python', 'scss', 'bash', 'sql', 'typescript', 'twig', 'yaml'])

const hostname = process.env.NODEJS_HOST;
const port     = process.env.NODEJS_PORT;

const server = http.createServer((request, response) => {
    console.info(`Received request: ${request.method} ${request.url}`);

    if (request.method !== 'POST') {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('Only POST requests are supported');
        return;
    }

    const queryData = url.parse(request.url, true).query;
    if (typeof queryData.language !== 'string') {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('`language` query param is required');
        return;
    }

    if (Object.keys(Prism.languages).includes(queryData.language) === false) {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('Unsupported language: ' + queryData.language);
        console.info('Unsupported language: ' + queryData.language);
        return;
    }

    let body = '';
    request.on('data', chunk => body += chunk);
    request.on('end', () => {
        try {
            const html = Prism.highlight(body, Prism.languages[queryData.language], queryData.language);

            response.statusCode = 200;
            response.setHeader('Content-Type', 'text/html; charset=utf-8');
            response.setHeader('Cache-control', 'public,max-age=30');
            response.end(html);
        } catch (e) {
            console.info(`Error processing request: ${e.message}`);
            response.statusCode = 400;
            response.setHeader('Content-Type', 'text/plain');
            response.end('prism error: ' + e.message);
        }
    });
});

server.listen(port, hostname, () => {
    console.info(`Server running at http://${hostname}:${port}/`);
});
