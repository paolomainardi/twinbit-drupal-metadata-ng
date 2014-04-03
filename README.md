# Twinbit entity frontend metadata classes

As written in the class documentation *Provides an abstract metadata wrapper allowing easy usage of the entity metadata*, but what does it means ?

Well, Drupal offers many ways to access entity fields data and attributes (ex: title attribute of a link field), one of them is based on MetadatWrapper object exposed by [EntityAPI module](https://drupal.org/project/entity), while it is very powerful, it is far from being easily used by frontend developers.

We create, this wrapper in order to simplify, as much as possible, the 