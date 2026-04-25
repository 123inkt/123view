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
