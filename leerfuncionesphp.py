import re

# Nombre del archivo PHP de entrada
archivo_entrada = "WebsiteController.php"

# Nombre del archivo de salida
archivo_salida = "nombres_funciones.txt"

# Expresión regular para buscar funciones
expresion_regular = r'(public |private )?function (\w+)\s*\(.*\)'

# Abrir el archivo de entrada en modo lectura
with open(archivo_entrada, "r") as entrada:
    with open(archivo_salida, "w") as salida:
        # Leer el contenido completo del archivo
        contenido = entrada.read()
        
        # Buscar coincidencias con la expresión regular
        coincidencias = re.finditer(expresion_regular, contenido)
        
        # Iterar a través de las coincidencias
        for coincidencia in coincidencias:
            # Obtener el modificador de acceso (si existe)
            modificador = coincidencia.group(1) if coincidencia.group(1) else "no especificado"
            
            # Obtener el nombre de la función
            nombre_funcion = coincidencia.group(2)
            
            # Escribir el resultado en el archivo de salida
            salida.write(f"Función: {nombre_funcion}, Modificador de Acceso: {modificador}\n")

print(f"Los nombres de las funciones se han guardado en {archivo_salida}")