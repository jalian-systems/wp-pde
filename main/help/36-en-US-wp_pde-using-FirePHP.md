WpPDE includes the server side [FirePHP]( "FirePHP") for debugging your plugins. When you export the plugin for
distribution, stub methods are created so that there is no need for removing the trace messages.

WpPDE creates a global variable $pde\_firephp and provides a method pde\_fb() - use these instead of creating a
FirePHP instance or fb().

WpPDE also calls ob\_start() and ob\_get\_clean() at using the 'init' and 'shutdown' hooks - so using FirePHP methods
works everywhere.

