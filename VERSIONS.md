## PCO CITIES Release Tags

PCO CITIES uses a three-part semantic versioning nomenclature within the
constraints of drupal.org's current capabilities. See
[this drupal.org issue](https://www.drupal.org/node/1612910) for the latest.

The three parts of our versioning system are MAJOR.FEATURE.SPRINT. However, due
to the current constraints of drupal.org, there is no separator between the
FEATURE and SPRINT digits. This also limits PCO CITIES to ten sprint releases
before releasing a new feature.

Given the following tag: 8.x-2.15:

|       |                              |
|-------|------------------------------|
| __8__ | Major version of Drupal Core |
| __x__ |  |
| __2__ | Major version of PCO CITIES |
| __1__ | Feature release of PCO CITIES + Increments with minor core release |
| __5__ | Sprint release between feature releases |

PCO CITIES typically makes a sprint release every four weeks. We'll also use
sprint releases to package new minor releases of Drupal Core with PCO CITIES
as they become available. When this happens, we will also increment the feature
release/minor version number of PCO CITIES - about once every six months.

<!-- Links Referenced -->

[lightning]:                  https://github.com/acquia/lightning
[wxt-project]:                https://github.com/drupalwxt/wxt-project
