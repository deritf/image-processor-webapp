# IMAGE CONVERTER APP - USER GUIDE
==================================

These instructions are intended for use with **Laragon**, but you can also run the application in other local development environments such as **XAMPP**, or similar setups that support PHP and Composer.

## REQUIREMENTS
---------------
- Laragon installed on your system (with Apache and PHP)
- Visual Studio Code (or another editor with terminal support)
- PHP 8.1 or higher
- Composer (dependency manager for PHP)
- Internet access for initial installation

## PROJECT LOCATION
-------------------
1. Copy the entire project folder to the following Laragon directory: C:\laragon\www\

2. It should be located like this: C:\laragon\www\image-processor-webapp\
(For XAMPP: `C:\xampp\htdocs\image-processor-webapp\`)

## INSTALLING DEPENDENCIES
--------------------------
1. Open Visual Studio Code and load the project folder as a workspace:

2. Open the terminal (`Ctrl + ñ` or via `Terminal > New Terminal` menu)

3. Type and run the following command: composer install

This will install the required dependencies in the `vendor/` folder.

## RUNNING THE APPLICATION
--------------------------
1. Open Laragon and make sure Apache is running.

2. In your browser, go to: http://localhost/image-processor-webapp/

3. You can now use the application. Remember:
- Place your images inside the `INPUT/` folder
- Select the desired output format, quality, and editing options
- Click the process button
- The converted images will be saved in the `OUTPUT/` folder


## Autor
--------------------
Derimán Tejera
