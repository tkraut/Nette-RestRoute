# REST route for [Nette Framework](http://nette.org) 
[![Build Status](https://travis-ci.org/newPOPE/Nette-RestRoute.png)](https://travis-ci.org/newPOPE/Nette-RestRoute)

Route automatically maps CRUD to Presenters and actions in the defined module.
And creates parameters which are accessible in Presenter.  

- format
- id (autodetected)
- associations (an array with associations)
- data (raw data from the request)
- query (an array of items from the query string)

## Format detection:
Variable ```$format``` is detected from HTTP header ```Accept```. If header is not present Route try detect format from the URL (```.../foo.json```). If no format is int the URL Route use a default format ```json```. 

## Installation:
The best way to install Nette-RestRoute is using  [Composer](http://getcomposer.org/):

```sh
$ composer require adamstipak/nette-rest-route
```

## Usage:

```php
use AdamStipak\RestRoute;

// $router is an instance of Nette\Application\Routers\RouteList  

// No parameters needed. Presenter name will be generated.
$router[] = new RestRoute;

// With module.
$router[] = new RestRoute('Api');

// With module and xml as a default format.
$router[] = new RestRoute('Api', 'xml');
```


First parameter is a name of the module where the route will sends an Request. URL prefix will be generated. See examples.
####Examples:
 
```
NULL      => /<generated presenter name>
'Api'     => /api/<generated presenter name>
'My:Api'  => /my/api/<generated presenter name>
...
```

Second parameter is a default format. By default the default format is ```json```.
RestRoute support only 2 formats:  

- json *(default)*  
- xml

## Examples

### Basic:
**URL:** ```/api/users``` &rarr; ```\ApiModule\UsersPresenter::read```   
**HTTP HEADER Accept:** ```application/json```  
**Method:** GET  
**Request body:** Empty  
**Params:**  

```
format = json
associations = array(0)
data = ""
query = array(0)
```

#### Flag ```readAll```
If the flag ```$useReadAllAction``` is enabled ```$route->useReadAll()``` RestRoute generates ```readAll``` as action instead of ```read```. Nette will call ```Presenter::readAll``` instead of ```Presenter::read```.

---
### Resource ID
**URL:** ```/api/users/123``` &rarr; ```\ApiModule\UsersPresenter::read```  
**HTTP HEADER Accept:** ```application/json```  
**Method:** GET  
**Request body:** Empty  
**Params:**  

```
format = json
id = 123
associations = array(0)
data = ""
query = array(0)
```
---
### Query params:
**URL:** ```/api/users?foo=bar&page=1``` &rarr; ```\ApiModule\UsersPresenter::read```  
**HTTP HEADER Accept:** ```application/json```  
**Method:** GET  
**Request body:** Empty  
**Params:**  

```
format = json
associations = array(0)
data = ""
query = array(
	foo => "bar"
	page => 1
)
```
---
### Create:
**URL:** ```/api/users``` &rarr; ```\ApiModule\UsersPresenter::create```  
**HTTP HEADER Accept:** ```application/json```  
**Method:** POST  
**Request body:**  

```json
{
	"foo": "bar",
	"nested": {
		"foo": "bar"	
	}
}
```
  
**Params:**  

```
format = json
associations = array(0)
data = {"foo": "bar", "nested": {"foo": "bar"}}
query = array(0)
```
---
### Update:
**URL:** ```/api/users/123``` &rarr; ```\ApiModule\UsersPresenter::update```  
**HTTP HEADER Accept:** ```application/json```    
**Method:** PUT  
**Request body:**  

```json
{
	"foo": "bar",
	"nested": {
		"foo": "bar"	
	}
}
```
  
**Params:**  

```
format = json
id = 123
associations = array(0)
data = {"foo": "bar", "nested": {"foo": "bar"}}
query = array(0)
```
---
### Delete:
**URL:** ```/api/users/123``` &rarr; ```\ApiModule\UsersPresenter::delete```  
**HTTP HEADER Accept:** ```application/json```   
**Method:** DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
id = 123
associations = array(0)
data = ""
query = array(0)
```
---
### Associations:
Last item (pair) before .<format> is main resource. Everything what is before the last item are associations ([apigee.com](http://apigee.com/about/)).

**URL:** ```/api/users/1/comments``` &rarr; ```\ApiModule\CommentsPresenter::read|create|update|delete```  
**HTTP HEADER Accept:** ```application/json```  
**Method:** GET, POST, PUT, DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
associations = array(
	users => 1
)
data = ""
query = array(0)
```

**URL:** ```/api/users/123/comments/456``` &rarr; ```\ApiModule\CommentsPresenter::read|create|update|delete```  
**HTTP HEADER Accept:** ```application/json```  
**Method:** GET, POST, PUT, DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
id = 456
associations = array(
	users => 123
)
data = ""
query = array(0)
```

**URL:** ```/api/users/1/blogs/2/comments``` &rarr; ```\ApiModule\CommentsPresenter::read|create|update|delete```  
**HTTP HEADER Accept:** ```application/json```  
**Method:** GET, POST, PUT, DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
id = 1
associations = array(
	users => 1
	blogs => 2
)
data = ""
query = array(0)
```

##Overriding methods PUT, DELETE

Methods ```PUT``` and ```DELETE``` can be overriden via:  

### HTTP header ```X-HTTP-Method-Override```
Example:

```
X-HTTP-Method-Override: (PUT|DELETE)
```

### Query param ```__method```
Example:

```
?__method=(PUT|DELETE)
```
