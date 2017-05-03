Installation
============

### Get the bundle using composer

Add GlavwebCompositeObjectBundle by running this command from the terminal at the root of
your Symfony project:

```bash
php composer.phar require glavweb/composite-object-bundle
```

### Enable the bundle

To start using the bundle, register the bundle in your application's kernel class:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Glavweb\CompositeObjectBundle\GlavwebCompositeObjectBundle(),
        // ...
    );
}
```
### Configure the bundle

This bundle was designed to just work out of the box. The only thing you have to enable the dynamic routes, add the following to your routing configuration file. 

```yaml
#  app/config/routing.yml

glavweb_composite_object:
    resource: "@GlavwebCompositeObjectBundle/Resources/config/routing.yml"
    prefix:   /
```