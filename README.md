# Python MCP server

Super simple implementation of reference python MCP for GPT-OSS built in tools.

More information can be found in [GPT-OSS repository](https://github.com/openai/gpt-oss?tab=readme-ov-file#python)

The model was trained to use a python tool to perform calculations and other actions as part of its chain-of-thought. 
During the training the model used a stateful tool which makes running tools between CoT loops easier. 
This reference implementation, however, uses a stateless mode.

> [!WARNING]
> 
> This implementation runs in a permissive Docker container which could be problematic in cases like prompt injections. It's serving as an example and you should consider implementing your own container restrictions in production.

**Requirements:**
 - Docker must be installed and the current user must have permission to run it.
 - The image `python:3.11` will be pulled automatically if missing.

## Development

If you need to modify or want to run/debug server locally you should: 
 - `git clone` repository 
 - run `composer install`
 - `./bin/python-mcp` contains server, while `./bin/console` holds Symfony console

To debug server you should use `npx @modelcontextprotocol/inspector`

```bash
npx @modelcontextprotocol/inspector ./bin/python-mcp
```

## Env variables
Underneath it's just Symfony CLI application so typical ENV variables can be used, but not recommended for running as binary.
```dotenv
### Set log level, default INFO, with log action level ERROR
LOG_LEVEL=info
### If you want to store logs somewhere else
APP_CACHE_DIR="${HOME}/.symfony/python-mcp/cache"
APP_BUILD_DIR="${HOME}/.symfony/python-mcp/build"
APP_LOG_DIR="${HOME}/.symfony/python-mcp/log"
```

## Installing and running MCP
