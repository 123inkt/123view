const http = require('http');
const url  = require('url');
// load common languages
const hljs = require('highlight.js/lib/common');
// supplement with custom languages
hljs.registerLanguage('ini', require('highlight.js/lib/languages/ini'));
hljs.registerLanguage('apache', require('highlight.js/lib/languages/apache'));
hljs.registerLanguage('twig', require('highlight.js/lib/languages/twig'));
hljs.registerLanguage('xml', require('highlight.js/lib/languages/xml'));

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

    if (hljs.listLanguages().includes(queryData.language) === false) {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('Unsupported language: ' + queryData.language);
        return;
    }

    let body = '';
    request.on('data', chunk => body += chunk);
    request.on('end', () => {
        try {
            response.statusCode = 200;
            response.setHeader('Content-Type', 'text/html; charset=utf-8');
            response.setHeader('Cache-control', 'public,max-age=30');
            response.end(hljs.highlight(body, {language: String(queryData.language), ignoreIllegals: true}).value);
        } catch (e) {
            response.statusCode = 400;
            response.setHeader('Content-Type', 'text/plain');
            response.end('highlightjs error: ' + e.message);
        }
    });
});

server.listen(port, hostname, () => {
    console.info(`Server running at http://${hostname}:${port}/`);
});
