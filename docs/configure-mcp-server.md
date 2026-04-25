# Configure 123view mcp server

## Copilot

1) Create access token at `/app/user/access-tokens`
1) Type: `http`
1) URL: `https://123view.my-host.nl/_mcp`
1) Headers: `{"Authorization": "Bearer 1234567890abcdefghijklmnopqrstuvwxyz"}`
1) Tools: `*`

### Manual configuration

Edit `~/.copilot/mcp-config.json`
```json
{
    "mcpServers": {
        "123view": {
            "type": "http",
            "url": "https://123view.my-host.nl/_mcp",
            "headers": {
                "Authorization": "Bearer 1234567890abcdefghijklmnopqrstuvwxyz"
            }
        }
    }
}
```

## Available tools
- `get-current-user`: Returns the id, name and email of the currently authenticated user.
- `get-code-review`: Find the first code review matching the given filters. All provided filters are applied as AND conditions. Returns null when no match is found.
- `get-code-reviews`: Search for code reviews using optional filters. All provided filters are applied as AND conditions. Returns up to 50 results ordered by most recently updated.
