### File structure

```
- drupal-qa/config/
-- phpstan.drupal-qa.neon - contains basic configuration for PHPStan to understand a Drupal codebase (used to be provided by DrupalCheck) plus Pronovix specific configurations and enhancements
-- skelentons/
--- phpstan.neon.dist - the initial PHPStan congfiguration that every new (or upgraded) project gets as a default and from that moment it belongs to the given project, it is not going to be changed by DrupalQA anymore. This it the file that downstream developers MUST commit to the repository. It can be used to add any _project-specific_ adjustments and changes. Its sole purpose is to include the phpstan.drupal-qa.neon and the phpstan-baseline.neon config files, nothing more.
--- phpstan-baseline.neon - an empty baseline file that is only there to be able to include it in phpstan.neon.dist and make sure that all projects have a baseline file that can be updated whenever it is neded. It also must be committed to VCS.
```

For (development) environment specific overrides `phpstan.neon` can be used (but it must not be committed to VCS) as the official PHPStan documentation also described it: https://phpstan.org/config-reference#config-file
