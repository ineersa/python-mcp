# Python MCP server

Super simple implementation of reference python MCP for GPT-OSS built-in tools.

More information can be found in [GPT-OSS repository](https://github.com/openai/gpt-oss?tab=readme-ov-file#python)

The model was trained to use a python tool to perform calculations and other actions as part of its chain-of-thought. 
During the training the model used a stateful tool which makes running tools between CoT loops easier. 
This reference implementation, however, uses a stateless mode.

> [!WARNING]
> 
> This implementation runs in a permissive Docker container, which could be problematic in cases like prompt injections. It's serving as an example and you should consider implementing your own container restrictions in production.

**Requirements:**
 - Docker must be installed, and the current user must have permission to run it.
 - The image `python:3.11` will be pulled automatically if missing.

## Installing and running MCP
To generate binary run `./prepare_binary.sh`, it should work on Linux.

To build binary, you have to install [box-project/box](https://github.com/box-project/box/blob/main/doc/installation.md#composer)
to generate PHAR.

Thanks to amazing projects like [Static PHP](https://static-php.dev/en/) and [FrankenPHP](https://frankenphp.dev/docs/embed/) we are able to run PHP applications as a single binary now.

The easiest way is to just download binary from releases for your platform.

## MCP config:
**STDIO** is only supported transport for now, just add entry to `mcp.json` with a path to binary
```json
{
    "command": "./dist/python-mcp",
    "args": [],
    "env": {
        "APP_LOG_DIR": "/tmp/.symfony/python-mcp/log"
    }
}
```
You can also use `python-mcp.phar` PHAR file.

If you want to use other transports use some wrapper for now, for example, [MCPO](https://github.com/open-webui/mcpo)

```bash
uvx mcpo --port 8000 -- ~/mcp/dist/python-mcp
```

## Env variables
Underneath it's just Symfony CLI application, so typical ENV variables can be used, but not recommended for running as binary.
```dotenv
### Set log level, default INFO, with log action level ERROR
LOG_LEVEL=info
# Where to store logs
APP_LOG_DIR="/tmp/mcp/python-mcp/log"
```

## Development

If you need to modify or want to run/debug a server locally, you should:
- `git clone` repository
- run `composer install`
- `./bin/python-mcp` contains server, while `./bin/console` holds Symfony console

To debug server you should use `npx @modelcontextprotocol/inspector`

```bash
npx @modelcontextprotocol/inspector ./bin/python-mcp
# for compiled version with log dir
npx @modelcontextprotocol/inspector -e APP_LOG_DIR="${HOME}/.symfony/python-mcp/log" ./dist/python-mcp
```
