-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-03-2022 a las 19:06:28
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `guias_dev`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `laravel`
--

CREATE TABLE `laravel` (
  `id` int(5) NOT NULL,
  `descrip` varchar(100) NOT NULL,
  `guia` varchar(250) NOT NULL,
  `parent` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `laravel`
--

INSERT INTO `laravel` (`id`, `descrip`, `guia`, `parent`) VALUES
(1, 'Tabla de ayudas para laravel>1', 'laravel', 0),
(3, 'instal composer laravel v1 > 4', 'composer [glogal] require laravel/installer; laravel new *miproyect', 0),
(4, 'instal composer laravel v2 > 1', 'composer create-project laravel/laravel *miproyecto --prefer-dist', 3),
(5, 'activar servicio laravel >1', 'php artisan serve', 0),
(7, 'Rutas en routes/web.php > 8', 'Route::get(\'/*hola\',fn(){= [view]*mundo}', 0),
(8, 'helpers resources/views/some.blade.php >1', 'ie @if (Route::has(\'login\')) @else <a href(\"{{url(\'/home\')}}\" ...@endif +laravel.com/docs/9.x/blade ', 7),
(9, 'Blade validacion variable >10 ', '@isset($title) {{$title}} @else {{\'No title\'}}@endisset', 0),
(10, 'Rutas views/some.blade.php Route::get ...{**}); >1', '** return view(view:\'test\',[\'title\'=>\'some\']);', 9),
(11, 'Controladores y Rutas>12', 'php artisan make:controller *micontroller', 0),
(12, 'app/Http/Controllers/Auth/micontroller.php class MiController extends Controller{**}', '** + index(){= view(view: \'welcome\')};', 11),
(13, 'Rutas en web.php > 0', ' Route::get(\'/\',\'MyController@index\'):', 12);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `laravel`
--
ALTER TABLE `laravel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
