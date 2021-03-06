<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Delimiters
    |--------------------------------------------------------------------------
    |
    | Change the block delimiter tags.
    |
    */
    'delimiters' => array(
        'tag_comment'  => array('{#', '#}'),
        'tag_block'    => array('{%', '%}'),
        'tag_variable' => array('{{', '}}'),
    ),

    /*
    |--------------------------------------------------------------------------
    | Accepts all Twig configuration options
    |--------------------------------------------------------------------------
    |
    | http://twig.sensiolabs.org/doc/api.html#environment-options
    |
    */
    'environment' => array(

        // When set to true, the generated templates have a __toString() method that you can use to display the generated nodes.
        // default: false
        'debug' => false,

        //  The charset used by the templates.
        // default: utf-8
        'charset' => 'utf-8',

        // The base template class to use for generated templates.
        // default: Twig_Template
        'base_template_class' => 'Twig_Template',

        // An absolute path where to store the compiled templates, or false to disable caching. If null
        // then the cache file path is used.
        // default: cache file storage path
        'cache' => null,

        // When developing with Twig, it's useful to recompile the template whenever the source code changes.
        // If you don't provide a value for the auto_reload option, it will be determined automatically based on the debug value.
        'auto_reload' => true,

        // If set to false, Twig will silently ignore invalid variables (variables and or attributes/methods that do not exist)
        // and replace them with a null value. When set to true, Twig throws an exception instead.
        // default: false
        'strict_variables' => false,

        // If set to true, auto-escaping will be enabled by default for all templates.
        // default: true
        'autoescape' => false,

        // A flag that indicates which optimizations to apply (default to -1 -- all optimizations are enabled; set it to 0 to disable)
        'optimizations' => -1,
    ),

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    |
    | List of Twig extensions that are made available to your Twig templates.
    | When an unknown function is called within your templates, Twig looks to these
    | extensions first.
    |
    */
    // NOTE: If you change this, make sure you clear your cache
    'extensions' => array(
        'TwigBridge\Extensions\Html',
    ),

    /*
    |--------------------------------------------------------------------------
    | Alias Extensions
    |--------------------------------------------------------------------------
    |
    | TwigBridge automatically gives you access to all classes and public methods
    | that you define as aliases in app/config/app.php
    |
    | It follows the format of class_method(...). So for example:
    |
    | hash_make -> calls Hash::make(...)
    | url_to    -> calls URL::to(...)
    |
    | These are called last, after built-in functions and extensions defined above.
    */
    // Do not allow all aliases to be accessed as Twig functions
    // NOTE: If you change this, make sure you clear your cache
    'disable_aliases' => false,

    // Give aliases shortcuts
    // Key   -> shortcut
    // Value -> alias
    'alias_shortcuts' => array(
        'url'    => 'url_to',
        'config' => 'config_get',
    ),
);