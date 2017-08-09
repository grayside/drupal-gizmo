# Gizmo

> A demo/example Drupal project which replaces grunt-drupal-tasks with a composer-based workflow.

When [grunt-drupal-tasks](https://github.com/phase2/grunt-drupal-tasks) got
its start, the idea was to combine frontend and backend needs for tooling in a
language and framework that both frontend and backend specializing developers
would find at least somewhat approachable. Over time, backend tooling evolved in
different directions (e.g., Composer) and frontend tooling greatly evolved in
sophistication.

This repository is an experiment in revisiting Drupal-based tooling with
the same mindset and best practices facilitated by grunt-drupal-tasks, but with
a focus on what might be produced by sticking closer to what composer has to offer.

## Pros and Cons

The benefits of switching to composer-based workflow over grunt-drupal-tasks.

Pro                             | Con
--------------------------------|-----
Much smaller file size          | Lose easy JSON project config
Many fewer files                | Harder to maintain Windows compatibility.
Has phpcbf support already      | Related generator projects need updates
Higher performance phpcs        | Existing projects need a transition plan
Able to use Core phpcs standard | -
Less code to maintain           | -
Easier to retrofit non-standard projects | -
Reduced user cognitive overhead | -
More process transparency       | -

## Functionality Comparison

Task          | GTD                 | Gizmo                                   
--------------|---------------------|-------------------
Build         | `grunt`             | `composer install` or `composer update`
Scaffold      | `grunt scaffold`    | `composer run-script project-scaffold`
Syntax Checks | `grunt validate`    | `composer test`
Analyze       | `grunt analyze`     | TBD
Behat         | `grunt behat`       | `composer run-script behat`
Theme Scripts | `grunt themes:sprocket:compile-theme` | `composer run-script theme-install`
Watch         | `grunt watch` (deprecated) | --
Help          | `grunt help`        | `composer run-script --list`

### Task: Build [`grunt`]
**Replacement:** `composer install` or `composer update`

> The default grunt-drupal-tasks process is responsible for the primary end-to-end pipeline of static code analysis, dependency management, and integrating custom code with the Drupal docroot.

In the composer.json file you may configure scripted steps to operate before or after the main process of either of those composer commands (amongst others).

### Task: Scaffold [`grunt scaffold`]
**Replacement:** `composer run-script project-scaffold`

> The scaffolding task is normally done as part of the main grunt build process,
but exists as a separate task to allow easily re-creating the scaffold if
actions were taken that removed the scaffolding (such as manually re-running
specific parts of the build, or deleting scaffolded symlinks as part of troubleshooting).

The name 'scaffold' is ambiguous alongside the drupal-scaffold task which downloads files such as index.php so was renamed to the more specific "project-scaffold".

This process is dependent on the package `kporras07/composer-symlinks`.
As part of this plugin, if you run the composer install with the `--no-dev` flag
it will perform recursive copy operations instead of symlinking.

### Syntax Checks [`grunt validate`]
**Replacement:** `composer test`

> The static analysis tasks verify syntax and coding style rules. GDT currently
supports eslint, phplint, and phpcs as part of this process.

* `test` is supported as a "built-in" optional script, so does not need the run-script argument in the command invocation. It was used because validate is
already a command-name in composer.
* phplint and phpcs are executed as composer dependencies, so they cannot be
executed before composer install.
* eslint remains **to be done**, because as a node-based dependency we need to
think about the best way to leverage it. Possibly it will be executed out of
theme dependencies or recommended as a global installation.

### Task: Behat [`grunt behat`]
**Replacement:** `composer run-script behat`

> Behat support in grunt-drupal-tasks provides dynamic configuration of the Zombie library, registration of all features to be tested against one or more configured Base URLs, and parallel execution to speed up the tests. In Drupal 8, the parallel execution has sometimes been found to lock up the system.

* The composer substitution is missing the alternate URLs functionality, but usage of the existing behavior is such that just having an environment variable override mechanism is more than sufficient.
* All dynamic configuration stuff remains to be done, but the basic execution of behat is working properly.

### Task: Analyze [`grunt analyze`]

**Replacement**: TBD

> grunt analyze performs more in-depth static analysis of code complexity. This includes basic style checking as well as simple metrics indicating such things as too many large functions. Unlike grunt validate, grunt analyze generates reports which can be used to generate visualizations or for comparison purposes.

As far as we know, grunt analyze is mostly ignored and it's reports rarely viewed even when built into provided tools.

We could certainly re-implement without grunt, but it would need a much clearer value proposition to users to be worth the effort.

### Task: Help [`grunt help`]
**Replacement:** `composer run-script --list`

> grunt help has tailored usage information about all the "officially" supported tasks for a project, including short descriptions and categorical groupings.

The composer equivalent is an uncategorized list of command-names. Existing grunt-drupal-tasks users seem potentially hit-or-miss on realizing this mechanism exists.

It is possible we could create a composer plugin to provide better composer script help support if this is really significant.

### Task: Theme Scripts [`grunt themes:themename:compile-theme`]
**Replacement:** `composer run-script theme-install`

> grunt has a facilitate to configure commands to be dispatched as shell scripts in the directory of the configured themes.

This can be easily replaced by configuring additional composer scripts on a per theme basis, and associate them as needed with other script events to ensure they are run after update steps.

## Loss of the Gruntconfig.json

One of the real niceties of grunt is the ability to use Gruntconfig.json to store complex configuration for the project.

However, while this is certainly a real barrier for BASH scripts, Composer is perfectly capable of supporting us in creating PHP scripts that can do JSON manipulation.

For cases where we need configuration then, our options in vaguely decreasing order of preference are:

* Leverage environment variables more.
* Use composer.json to hold configuration.
* Create new, purpose-specific JSON configuration files and access them with PHP scripts.
* Create variable assignment bash scripts as ad hoc config files that can be sourced by BASH scripts. Not well-structured but effective.

## Customizing Operations

Because grunt-drupal-tasks focused on being a configurable solution it require various kinds of hacking to insert or append tasks. With the composer-driven "steps" for each scripted operation, it is really just a matter of tweaking the composer.json in your project.

This means there is less "black-box" consistency to keep projects in alignment unless developers really want to make a change, but it also dramatically increases the transparency of what is happening by consolidating those details into simple array structures in a single file.

## To Be Done

* [ ] Fix behat support around environment-specific base URLs
* [ ] Determine eslint support
* [ ] Windows compatibility
    * `cp` in composer.json and kporras07/composer-symlinks
    * `sed` in composer.json
    * Stream redirection in composer.json
    * Bash style conditional logic in composer.json.
* [ ] kporras07/composer-symlinks needs a feature enhancement to convert all top-level items in a directory to symlinks inside the destination-as-directory.

## Included Scripts

* **drupal-scaffold:** Retrieve needed files to run Drupal that are not included in the drupal/core package. This is used as needed in `composer install` and `composer update`.
* **project-scaffold:** Build the development scaffolding with custom code. This is used as needed in `composer install` and `composer update`.
* **phplint:** Run php linting with parallel-lint. This is called by the `test` script.
* **phpcs:** Run phpcs coding style checks against targeted directories. This is called by the `test` script.
* **phpcbf:** Run phpcbf coding style autocorrection against targeted directories.
* **test:** Run syntax validation checks against custom code.
* **behat:** Run behat behavioral testing.
* **core-asset-update:** Copy Drupal configuration files into custom code, adjusting as needed for use. This is used in `composer update`.
* **outdated-safe:** Check for presumably easier/safer dependency changes that should be updated on a more routine basis.
* **update-core:** Command to update the core dependency. For projects built on a distribution, swap in the distribution name, such as `acquia/lightning`.
* **post-install-cmd:** Composer event run after `composer install` when a lockfile is present.
* **post-update-cmd:** Composer event run after `composer update` or `composer install` without a lockfile present.
* **theme-install:** Runs secondary theme installation steps on `composer update`.
