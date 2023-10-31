# Nombre del archivo de entrada
archivo_entrada = "web.php"

# Nombre del archivo de salida
archivo_salida = "archivo_salida.txt"

# Abrir el archivo de entrada en modo lectura
with open(archivo_entrada, "r") as entrada:
    # Abrir el archivo de salida en modo escritura
    with open(archivo_salida, "w") as salida:
        # Leer cada línea del archivo de entrada
        for linea in entrada:
            # Eliminar espacios en blanco al principio y al final de la línea
            linea = linea.strip()
            # Verificar si la línea comienza con "Route::get" y termina con ";"
            if linea.startswith("Route::get") and linea.endswith(";"):
                # Guardar la línea en el archivo de salida
                salida.write(linea + "\n")

print("Líneas que comienzan con 'Route::get' y terminan con ';' han sido guardadas en", archivo_salida)
