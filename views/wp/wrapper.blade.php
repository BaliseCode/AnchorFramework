<!DOCTYPE html>
<html <?php language_attributes() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php wp_head(); ?>
    @yield('head')
</head> 
<body <?php body_class(); ?>>
    @yield('body')
    <?php wp_footer(); ?>
</body>
</html>
