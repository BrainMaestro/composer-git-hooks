# composer-git-hooks
[![Travis](https://img.shields.io/travis/BrainMaestro/composer-git-hooks.svg?style=flat-square)](https://travis-ci.org/BrainMaestro/composer-git-hooks)
[![Packagist](https://img.shields.io/packagist/v/brainmaestro/composer-git-hooks.svg?style=flat-square)](https://packagist.org/packages/brainmaestro/composer-git-hooks)
> Manage git hooks easily in your composer configuration. This package makes it easy to implement a consistent project-wide usage of git hooks.

## Install

Add hooks to the `scripts` section of your `composer.json`.

```json
{
  "scripts": {
    "pre-commit": "phpunit",
    "post-commit": "echo committed",
    "pre-push": "phpunit && echo pushing!",
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

After installation is complete, run `cghooks add`
to add all the valid git hooks that have been specified in the composer config.

Option | Description | Command
------ | ----------- | -------
`force` | Override any existing git hooks | `cghooks add --force`
`no-lock` | Do not create a lock file | `cghooks add --no-lock`
`ignore-lock` | Add the lock file to .gitignore | `cghooks add --ignore-lock`

The `lock` file contains a list of all added hooks.

### Removing Hooks

Hooks can be easily removed with `cghooks remove`. This will remove all the hooks that were specified in the composer config.

Hooks can also be removed by passing them as arguments. The command `cghooks remove pre-commit post-commit` which will remove the `pre-commit` and `post-commit` hooks.

Option | Description | Command
------ | ----------- | -------
`force` | Delete hooks without checking the lock file | `cghooks remove --force`


**CAREFUL**: If the lock file was tampered with or the force option was used, hooks that already existed before using this package, but were specified in the composer scripts config will be removed as well. That is, if you had a previous `pre-commit` hook, but your current composer config also has a `pre-commit` hook, these options will caused the command to remove your initial hook.


### Listing hooks

Hooks can be listed with the `cghooks list-hooks` command. This basically checks composer config and list the hooks that actually have files.

## Related
- [husky](https://github.com/typicode/husky)


## License
MIT © Ezinwa Okpoechi
