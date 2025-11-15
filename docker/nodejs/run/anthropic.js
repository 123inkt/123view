const http = require('http');
const sdk  = require("@anthropic-ai/claude-agent-sdk");

const hostname     = process.env.NODEJS_HOST ?? '127.0.0.1';
const port         = process.env.ANTHROPIC_PORT ?? 3010;
const model        = process.env.ANTHROPIC_MODEL ?? 'claude-sonnet-4-5';
const systemPrompt = "You are an expert software developer.\n" +
        "You're aware of programming paradigms like DRY, YAGNI and SOLID.\n" +
        "Only give comments when there's a high likelihood that there is an actual issue.\n" +
        "Focus on code quality, readability, maintainability, performance, security, and adherence to best practices.\n" +
        "Ensure comments are concise and relevant to the code provided.\n" +
        "Prioritize coding errors, potential bugs, and best practices.\n" +
        "Skip code review comments with low confidence.\n" +
        "Skip code review comments with code errors that could be picked up by a linter, static analysis tool, or unit test.\n" +
        "Some reviews may not contain any issues for you to comment on.\n" +
        "Use AGENTS.md if present for context.\n";

const server = http.createServer(async (request, response) => {
    console.info(`Received request: ${request.method} ${request.url}`);

    if (request.method !== 'POST' || request.url !== '/query') {
        response.statusCode = 400;
        response.setHeader('Content-Type', 'text/plain');
        response.end('Only POST requests are supported for /query endpoint');
        return;
    }

    let body = '';
    request.on('data', chunk => body += chunk);
    request.on('end', async () => {
        const data = JSON.parse(body);
        console.log('Received review request:', data);

        const resultQuery = sdk.query({
            prompt: data.review,
            options: {
                model: model,
                allowedTools: ['ls', 'find', 'grep'],
                cwd: data.projectDir,
                systemPrompt: systemPrompt,
            }
        });

        let result = null;
        for await (const message of resultQuery) {
            if (message.type === 'result') {
                result = message.result;
            } else {
                console.log("\n----------------- message -----------------");
                console.log(message);
            }
        }

        if (result === null) {
            response.statusCode = 404;
            response.setHeader('Content-Type', 'text/plain');
            response.end('');
        }

        response.statusCode = 200;
        response.setHeader('Content-Type', 'text/markdown; charset=utf-8');
        response.end(result);
    });
});

server.timeout        = 120000;
server.headersTimeout = 120000;
server.requestTimeout = 120000;
server.listen(port, hostname, () => {
    console.info(`Server running at http://${hostname}:${port}/`);
});
