# composer-git-hooks
[![Travis](https://img.shields.io/travis/BrainMaestro/composer-git-hooks.svg?style=flat-square)](https://travis-ci.org/BrainMaestro/composer-git-hooks)
[![Packagist](https://img.shields.io/packagist/v/brainmaestro/composer-git-hooks.svg?style=flat-square)](https://packagist.org/packages/brainmaestro/composer-git-hooks)
> Manage git hooks easily in your composer configuration. This package makes it easy to implement a consistent project-wide usage of git hooks. Specifying hooks in the composer file makes them available for every member of the project team. This provides a consistent environment and behavior for everyone which is great.

## Install

Add a `hooks` section to the `extra` section of your `composer.json` and add the hooks there. The previous way of adding hooks to the `scripts` section of your `composer.json` is still supported, but this way is cleaner if you have many scripts.

```json
{
  "extra": {
    "hooks": {
      "pre-commit": "phpunit",
      "post-commit": "echo committed",
      "pre-push": "phpunit && echo pushing!",
      "...": "..."
    }
  }
}
```

Then install the library with
```sh
composer require --dev brainmaestro/composer-git-hooks
```

This installs the `cghooks` binary to your `vendor/bin` folder. If this folder is not in your path, you will need to preface every command with `vendor/bin/`.

### Optional Configuration

#### Shortcut

Add a `cghooks` script to the `scripts` section of your `composer.json` file. That way, commands can be run with `composer cghooks ${command}`. This is ideal if you would rather not edit your system path.

```json
{
  "scripts": {
    "cghooks": "vendor/bin/cghooks",
    "...": "..."
  }
}
```

#### Composer Events

Add the following events to your `composer.json` file. The `cghooks` commands will be run every time the events occur. Go to [Composer Command Events](https://getcomposer.org/doc/articles/scripts.md#command-events) for more details about composer's event system.

```json
{
  "scripts": {
    "post-install-cmd": "vendor/bin/cghooks add --ignore-lock",
    "post-update-cmd": "vendor/bin/cghooks update",
    "...": "..."
  }
}
```

## Usage

All the following commands have to be run in the same folder as your `composer.json` file.

### Adding Hooks

After installation is complete, run `cghooks add`
to add all the valid git hooks that have been specified in the composer config.

Option | Description | Command
------ | ----------- | -------
`no-lock` | Do not create a lock file | `cghooks add --no-lock`
`ignore-lock` | Add the lock file to .gitignore | `cghooks add --ignore-lock`
`force-win` | Force windows bash compatibility | `cghooks add --force-win`

The `lock` file contains a list of all added hooks.

### Updating Hooks

The update command which is run with `cghooks update` basically ignores the lock file and tries to add hooks from the composer lock. This is similar to what the `--force` option for the `add` command did. This command is useful if the hooks in the `composer.json` file have changed since the first time the hooks were added.

### Removing Hooks

Hooks can be easily removed with `cghooks remove`. This will remove all the hooks that were specified in the composer config.

Hooks can also be removed by passing them as arguments. The command `cghooks remove pre-commit post-commit` which will remove the `pre-commit` and `post-commit` hooks.

Option | Description | Command
------ | ----------- | -------
`force` | Delete hooks without checking the lock file | `cghooks remove --force`

**CAREFUL**: If the lock file was tampered with or the force option was used, hooks that already existed before using this package, but were specified in the composer scripts config will be removed as well. That is, if you had a previous `pre-commit` hook, but your current composer config also has a `pre-commit` hook, this option will cause the command to remove your initial hook.


### Listing hooks

Hooks can be listed with the `cghooks list-hooks` command. This basically checks composer config and list the hooks that actually have files.

#### Common Options

The following options are common to all commands.

Option | Description | Command
------ | ----------- | -------
`git-dir` | Path to git directory | `cghooks ${command} --git-dir='/path/to/.git'`

### Testing Hooks

Hooks can be tested with `cghooks ${hook}` before adding them. Example `cghooks pre-commit` runs the `pre-commit` hook.

## Related
- [husky](https://github.com/typicode/husky)


## License
MIT © Ezinwa Okpoechi
