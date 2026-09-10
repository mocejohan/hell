import json
import sys
from impacket import tds

try:
    host = sys.argv[1] if len(sys.argv) > 1 else '172.16.2.10'
    db = sys.argv[2] if len(sys.argv) > 2 else 'InventariosSQL2018'
    user = sys.argv[3] if len(sys.argv) > 3 else 'sa'
    password = sys.argv[4] if len(sys.argv) > 4 else 'Pa$$w0rd'
    termino = sys.argv[5] if len(sys.argv) > 5 else ''

    if not termino:
        print(json.dumps({'error': 'No se proporcionó número de inventario'}))
        sys.exit(1)

    m = tds.MSSQL(host, 1433)
    m.connect()
    res = m.login(db, user, password, '', None, False)

    if not res:
        print(json.dumps({'error': 'Login fallido a la base de datos de SQL Server'}))
        sys.exit(1)

    # Escapar comillas simples para prevenir inyección SQL
    termino_safe = termino.replace("'", "''")

    query = (
        "SELECT TOP 1 "
        "i.[Número de Inventario] as numero_inventario, "
        "i.[Número Inventario Anterior] as numero_inventario_anterior, "
        "i.[Descripción del Bien] as equipo, "
        "i.[Marca] as marca, "
        "i.[Modelo] as modelo, "
        "i.[Número de Serie] as serie, "
        "i.[Ubicación] as ubicacion, "
        "r.[Nombre de Usuario] as resguardatario "
        "FROM dbo.Inventarios i "
        "LEFT JOIN dbo.Resguardos r ON i.[Número de Inventario] = r.[Número de Inventario] "
        "WHERE i.[Número de Inventario] = '" + termino_safe + "'"
    )
    m.sql_query(query)

    data = None
    for r in m.rows:
        inv = str(r['numero_inventario'] or '').strip()
        inv_ant = str(r['numero_inventario_anterior'] or '').strip()
        equipo = str(r['equipo'] or '').strip()
        marca_val = str(r['marca'] or '').strip()
        modelo_val = str(r['modelo'] or '').strip()
        serie_val = str(r['serie'] or '').strip()
        ubicacion_val = str(r['ubicacion'] or '').strip()
        resguardatario_val = str(r['resguardatario'] or '').strip()

        # Limpiar valores tipo 'NULL' como texto
        if marca_val.upper() == 'NULL' or marca_val == '':
            marca_val = None
        if modelo_val.upper() == 'NULL' or modelo_val == '':
            modelo_val = None
        if serie_val.upper() == 'NULL' or serie_val == '':
            serie_val = None
        if ubicacion_val.upper() == 'NULL' or ubicacion_val == '':
            ubicacion_val = None
        if inv_ant.upper() == 'NULL' or inv_ant == '':
            inv_ant = None
        if resguardatario_val.upper() == 'NULL' or resguardatario_val == '':
            resguardatario_val = None

        data = {
            'numero_inventario': inv,
            'numero_inventario_anterior': inv_ant,
            'equipo': equipo if equipo else None,
            'marca': marca_val,
            'modelo': modelo_val,
            'serie': serie_val,
            'ubicacion': ubicacion_val,
            'resguardatario': resguardatario_val,
        }
        break  # Solo necesitamos el primer registro

    m.disconnect()

    if data:
        print(json.dumps(data, ensure_ascii=False))
    else:
        print(json.dumps({'not_found': True}))

except Exception as e:
    print(json.dumps({'error': str(e)}))
    sys.exit(1)
