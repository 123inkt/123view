const http = require('http');
const sdk = require("@anthropic-ai/claude-agent-sdk");

const hostname = process.env.NODEJS_HOST;
const port     = process.env.ANTHROPIC_PORT;

const server = http.createServer(async (request, response) => {
    console.info(`Received request: ${request.method} ${request.url}`);

    if (request.method !== 'GET' || request.url !== '/') {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('Only GET requests are supported');
        return;
    }
    console.log('----------------starting-----------------');

    const resultQuery = sdk.query({
        prompt: 'Review the following code for potential issues and suggest improvements:\n\n```javascript\nfunction add(a, b) {\n  return a + b;\n}\n\nconsole.log(add(2, 3));\n```',
        options: {
            model: 'claude-sonnet-4-20250514',
            allowedTools: ['ls', 'find', 'grep'],
            systemPrompt: 'You are a helpful coding assistant that reviews code snippets for potential issues and suggests improvements. Use AGENTS.md if present for context',
        }
    });

    let result = null;
    for await (const message of resultQuery) {
        if (message.type === 'result') {
            console.log('response', message.result);
            result = message.result;
        } else {
            console.log('other type', message.type);
        }
    }

    console.log('----------------finished-----------------');


    response.statusCode = 200;
    response.setHeader('Content-Type', 'text/plain; charset=utf-8');
    response.end(result);
});

server.timeout = 120000;
server.headersTimeout = 120000;
server.requestTimeout = 120000;
server.listen(port, hostname, () => {
    console.info(`Server running at http://${hostname}:${port}/`);
});
