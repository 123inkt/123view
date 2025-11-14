const http = require('http');
const sdk = require("@anthropic-ai/claude-agent-sdk");

const hostname = process.env.NODEJS_HOST;
const port     = process.env.ANTHROPIC_PORT;

const server = http.createServer(async (request, response) => {
    console.info(`Received request: ${request.method} ${request.url}`);

    if (request.method !== 'GET') {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('Only GET requests are supported');
        return;
    }
    console.log('----------------starting-----------------');

    const result = sdk.query({
        prompt: 'Review the following code for potential issues and suggest improvements:\n\n```javascript\nfunction add(a, b) {\n  return a + b;\n}\n\nconsole.log(add(2, 3));\n```',
        options: {
            model: 'claude-sonnet-4-20250514',
            allowedTools: ['ls', 'find', 'grep'],
            systemPrompt: 'You are a helpful coding assistant that reviews code snippets for potential issues and suggests improvements. Use AGENTS.md if present for context',
        }
    });

    let msg = '';
    for await (const message of result) {
        msg += result.result;
        console.log(result.result ?? null);
    }

    response.statusCode = 200;
    response.setHeader('Content-Type', 'text/plain; charset=utf-8');
    response.end(msg);
});

server.listen(port, hostname, () => {
    console.info(`Server running at http://${hostname}:${port}/`);
});
