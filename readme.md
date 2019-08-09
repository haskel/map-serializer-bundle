Map Serializer Bundle
==================

Symfony Bundle for map-serialiser component

## Installation
```bash
composer require haskel/map-serializer-bundle
```

## Usage
Add bundle to `config/bundles.php`
```php
Haskel\MapSerializerBundle\MapSerializerBundle::class => ['all' => true]
```
<br>

Add yaml schemas to `config/map_serializer`
Example `config/map_serializer/User.yaml` :
```yaml
App\Entity\User:
  default:
    id: 'int'
    username: 'string'
    isActive: 'boolean'
    group: 'default'
    role: 'string'
    name: 'string'
    email: 'string'
    
  simple:
    id: 'int'
    name: 'string'
    
  public:
    username: 'string'
    email: 'string'
    name: 'string'
```

Well, your response will be processed by `\Haskel\MapSerializerBundle\EventListener\ResponseListener`
