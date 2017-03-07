# composer-git-hooks
> Manage git hooks easily in your composer configuration. This package makes it easy to implement a consistent project-wide usage of git hooks. 

## Install

Add hooks to the `scripts` section of your `composer.json`.

```json
{
  "scripts": {
    "pre-commit": "phpunit test",
    "post-commit": "echo committed",
    "pre-push": "phpunit test && echo pushing!",
    "...": "..."
  }
}
```

Then install the library with
```sh
composer require --dev brainmaestro/composer-git-hooks
```

## Usage

### Adding Hooks

After installation is complete, run `vendor/bin/cghooks add`
to add all the valid git hooks that have been specified in the composer config.

If a hook that was specified in the scripts already exists, it will not be overridden. To override it, pass the `--force` or `-f` flag. So the command will be `vendor/bin/cghooks add --force`

### Removing Hooks

Hooks can be easily removed with `vendor/bin/cghooks remove`. This will remove all the hooks that were specified in the composer config.

**CAREFUL**: Hooks that already existed before using this package, but were specified in the composer scripts config will be removed as well. That is, if you had a previous `pre-commit` hook, but your current composer config also has a `pre-commit` hook, this command will remove your initial hook.

Hooks can also be removed by passing them as arguments. The command `vendor/bin/cghooks remove pre-commit post-commit` which will remove the `pre-commit` and `post-commit` hooks.

### Listing hooks

Hooks can be listed with the `vendor/bin/cghooks list-hooks` command. This basically checks composer config and list the hooks that actually have files.

## Related
- [husky](https://github.com/typicode/husky)


## License
MIT © Ezinwa Okpoechi
