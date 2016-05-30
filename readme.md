
# Table of Contents:

- ##### [Introduction](#introduction)
- ##### [Requirements](#requirements)
- ##### [What's it made of?](#whats-it-made-of)
- ##### [Dependencies](#dependencies)
- ##### [Installation](#installation)
- ##### [Features](#features)
	###### [Data Types](#data-types)
- ##### [Usage](#usage)

# Introduction

Mitter is a model sitter for your Lavavel 5.1.x application. Mitter will make the process of maintaining your model's entities hassle-free. You will be able manage your models and have a bird's eye view on every entity if necsessary.(The necsessity for such thing is basically what brought you here, right?). Mitter builds index, create and edit pages and handles the persistence of modifications to your entities for you.

# Requirements

For Mitter to method, a few requirements have to be met (not to be confused with dependencies).

##### laravelcollective/html:

Mitter uses the facades provided by this package to generate the forms. See laravelcollective/html [website](https://laravelcollective.com) for more details and installation instructions.
 
##### A Twitter Bootstrap Admin panel

The partials that are conditionally included inside the form view are stylized with bootstrap classes and can be restylized dynamically. That is why you will need a Bootstrap admin panel.

# Dependencies

Mitter uses a number of open source projects for its interface to work properly:
 - ###### [Twitter Bootstrap](https://getbootstrap.com) - a framework for developing responsive, mobile first web applications.
 - ###### [jQuery](https://jquery.com/) - jQuery JavaScript Library.
 - ###### [select2](https://select2.github.io) -  a jQuery based replacement for select boxes
 - ###### [Eonasdan/bootstrap-datetimepicker](https://github.com/Eonasdan/bootstrap-datetimepicke) - Date/time picker widget based on Twitter Bootstrap

# What's it made of?

- FormBuilder: generates the required form for creating and editing your model's entities and their relations.
- FormSaver: handles the persistence of your model entities and their relations.
- IndexBuilder: generates a table to be populated by your desired model's associated records.
- BaseController: The abstract BaseController class provides a resource controller for all models.
- BaseApiController: provides a simple [id,text] API for models to be used on forms


# Installation

Install Mitter by executing the composer command:
```
composer require Yaim\Mitter "dev-master"
```
Next, update your ```config/app.php``` file by adding Mitter's service provider. Add the following line to your ```providers``` array:
```
Yaim\Mitter\MitterServiceProvider::class,
```

As mentioned before, Mitter requires a bootstrap Admin panel. If you don't have one, we have bundled Mitter with the assets required to get you started. If you already have a bootstrap Admin panel, you can skip this step:

Copy the contents of the Admin folder to your applications resources/views directory.

Copy the contents of the public folder to the public folder in your application directory.

Run the ```artisan``` command:

```
php artisan vendor:publish
```

Next, you will need to create your BaseController. Mitter is bundled with a BaseController which you can use as an example on how to do so.

After this, you'll need to create your model controllers used by Mitter to populate the index or your forms. These controllers are basically an array reflecting your model's data structure and its relations. See "Features" for more details on what's available out of the box. You can add your own features if need be. (Obviously!)

For models to be able to get handled by Mitter, you'll need to use the Mitter's BaseModelTrait and additionally Eloquent's SoftDeletes trait in your models like so:

```
use Yaim\Mitter\BaseModelTrait as MitterTrait;
```

And inside your model's body:

```
use MitterTrait;
```

Finally, you should add these routes to your applcations ```routes.php``` file:

```
$config = config('yaim.routes');

Route::get($config['panelPrefix'] . '/{model}', '\Yaim\Mitter\Controllers\MitterController@index');
Route::get($config['panelPrefix'] . '/{model}/create', '\Yaim\Mitter\Controllers\MitterController@create');
Route::get($config['panelPrefix'] . '/{model}/{id}', '\Yaim\Mitter\Controllers\MitterController@show');
Route::get($config['panelPrefix'] . '/{model}/{id}/edit', '\Yaim\Mitter\Controllers\MitterController@edit');
Route::post($config['panelPrefix'] . '/{model}', '\Yaim\Mitter\Controllers\MitterController@store');
Route::put($config['panelPrefix'] . '/{model}/$id', '\Yaim\Mitter\Controllers\MitterController@store');
Route::delete($config['panelPrefix'] . '/{model}/{id}', '\Yaim\Mitter\Controllers\MitterController@destroy');
```

# Features

## Form Generator

You can view the ```FormBuilder``` class to get a gist of how the forms are generated dynamically through the defined methods. However, you can read on for a brief description of what you're dealing with:
The ```FormBuilder``` class provides you with out-of-the-box methods to populate the create/edit views. It does so by fetching your model's name and the appropriate instance based on the id attribute passed to the callback. Then, it will look at the data structure of the resource controller, fetch the rows and return the appropriate fields based on the data types and passes them to the appropriate partials. By passing in the structure to the ```formContent()```, Mitter will determine the fields it needs to populate the HTML form, and passes the fields to the ```rowFetcher()``` method. If the model instance has previously filled data, it will populate the fields for editing.

### Data Types

The methods associated with the data types will initially extract the fields for the current instance and then handle them according to the set values.

- ##### Text

The ```text()``` method will look if previous data is associated with the current instance and passes them to ```mitter.partials.text```. It will also look if a width attribute is set inside the fields array. If so, it will override the default width value of the text field set inside the partial according to that value.

- ##### Link

The anchor tags are handled through the ```link()``` method. It will

- ##### Image
- ##### Password

Not working as intended. Will be updated later.

- ##### Date/Time
- ##### Date
- ##### Time
- ##### Json
- ##### Select

This method generates a [select2](https://select2.github.io) select box from ```mitter.partials.select``` with the selected option, and all of the available options.

- ##### TextArea
- ##### Editor
- ##### Boolean

Makes a checkbox with its current state.

- ##### Ajax Tags
- ##### Create Ajax Tag
- ##### Ajax Guess
- ##### Create Ajax Guess
- ##### Divider

Drops a horizontal line preceded by the title name.

- ##### Locked
- ##### Hidden

All of these types' corresponding methods for returning the correct partials associated with them take $extraAttributes, $continious, $name, $title, $field and $oldData as their arguments. However, additional to the aforementioned arguments, the ajaxGuess and a few others will take $model and $createNew arguments as well. Here is a description of what is what:

###### $title

This is the lable for the field.

###### $name

This is the name associated with the field's column in the database.

###### $field

Contains your model's data structure which will be iterated by ```formContent()``` and then passed to ```rowFetcher()```.

###### $oldData

The data associated with the current model instance.(if exists)

After the form is generated, the admins will be able to edit the entity and its associated relations.

## Persisting the Forms

After the view for the required form was generated with the FormBuilder class, you will be able to persist your modifications of the model instance when you're done editing. This is handled by the FormSaver Class.

There's not much there for the user to know about this class, however you can take a look at the code to figure out what's happening behind the scenes as it is thoroughly commented.(TO DO!)

## Building Indexes



# Usage

