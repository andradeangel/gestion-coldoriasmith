<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API REST - Colegio Dora Schmidt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .test-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .test-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .test-info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Test de API REST - Sistema Colegio</h1>
        <p class="lead">Pruebas de los endpoints de la API REST para padres</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Pruebas de API</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary mb-2" onclick="testGetPadres()">GET /api/padres</button><br>
                        <button class="btn btn-success mb-2" onclick="testCreatePadre()">POST /api/padres</button><br>
                        <button class="btn btn-info mb-2" onclick="testGetPadreById()">GET /api/padres/{id}</button><br>
                        <button class="btn btn-warning mb-2" onclick="testUpdatePadre()">PUT /api/padres/{id}</button><br>
                        <button class="btn btn-danger mb-2" onclick="testDeletePadre()">DELETE /api/padres/{id}</button><br>
                        <button class="btn btn-secondary mb-2" onclick="testAsociarEstudiante()">POST /api/padres/{id}/asociar</button><br>
                        <button class="btn btn-dark mb-2" onclick="testDesasociarEstudiante()">DELETE /api/padres/{id}/desasociar</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Resultados de Pruebas</h5>
                    </div>
                    <div class="card-body">
                        <div id="testResults"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Datos de Prueba</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Padre de Prueba:</h6>
                                <pre id="padreData"></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Respuesta de la API:</h6>
                                <pre id="apiResponse"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let testPadreId = null;
        
        function addTestResult(message, type = 'info') {
            const resultsDiv = document.getElementById('testResults');
            const resultDiv = document.createElement('div');
            resultDiv.className = `test-result test-${type}`;
            resultDiv.innerHTML = `<strong>${new Date().toLocaleTimeString()}</strong> - ${message}`;
            resultsDiv.appendChild(resultDiv);
            resultsDiv.scrollTop = resultsDiv.scrollHeight;
        }
        
        function showData(title, data) {
            document.getElementById('padreData').textContent = title + ':\n' + JSON.stringify(data, null, 2);
        }
        
        function showResponse(data) {
            document.getElementById('apiResponse').textContent = JSON.stringify(data, null, 2);
        }
        
        async function apiRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                const data = await response.json();
                return { success: response.ok, data, status: response.status };
            } catch (error) {
                return { success: false, data: { error: error.message }, status: 0 };
            }
        }
        
        async function testGetPadres() {
            addTestResult('Probando GET /api/padres...', 'info');
            
            const result = await apiRequest('/api/padres');
            
            if (result.success) {
                addTestResult(`✅ GET /api/padres exitoso - ${result.data.total} padres encontrados`, 'success');
                showResponse(result.data);
            } else {
                addTestResult(`❌ GET /api/padres falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        async function testCreatePadre() {
            const nuevoPadre = {
                nombres: 'Juan',
                apellidos: 'Pérez Test',
                email: 'juan.perez.test@email.com',
                password: 'password123'
            };
            
            showData('Datos a enviar', nuevoPadre);
            addTestResult('Probando POST /api/padres...', 'info');
            
            const result = await apiRequest('/api/padres', {
                method: 'POST',
                body: JSON.stringify(nuevoPadre)
            });
            
            if (result.success) {
                testPadreId = result.data.data.id_padre;
                addTestResult(`✅ POST /api/padres exitoso - Padre creado con ID: ${testPadreId}`, 'success');
                showResponse(result.data);
            } else {
                addTestResult(`❌ POST /api/padres falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        async function testGetPadreById() {
            if (!testPadreId) {
                addTestResult('❌ No hay ID de padre para probar. Ejecute primero "Crear Padre"', 'error');
                return;
            }
            
            addTestResult(`Probando GET /api/padres/${testPadreId}...`, 'info');
            
            const result = await apiRequest(`/api/padres/${testPadreId}`);
            
            if (result.success) {
                addTestResult(`✅ GET /api/padres/${testPadreId} exitoso`, 'success');
                showResponse(result.data);
            } else {
                addTestResult(`❌ GET /api/padres/${testPadreId} falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        async function testUpdatePadre() {
            if (!testPadreId) {
                addTestResult('❌ No hay ID de padre para probar. Ejecute primero "Crear Padre"', 'error');
                return;
            }
            
            const datosActualizacion = {
                nombres: 'Juan Carlos',
                apellidos: 'Pérez Actualizado',
                email: 'juan.carlos.actualizado@email.com'
            };
            
            showData('Datos a actualizar', datosActualizacion);
            addTestResult(`Probando PUT /api/padres/${testPadreId}...`, 'info');
            
            const result = await apiRequest(`/api/padres/${testPadreId}`, {
                method: 'PUT',
                body: JSON.stringify(datosActualizacion)
            });
            
            if (result.success) {
                addTestResult(`✅ PUT /api/padres/${testPadreId} exitoso`, 'success');
                showResponse(result.data);
            } else {
                addTestResult(`❌ PUT /api/padres/${testPadreId} falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        async function testAsociarEstudiante() {
            if (!testPadreId) {
                addTestResult('❌ No hay ID de padre para probar. Ejecute primero "Crear Padre"', 'error');
                return;
            }
            
            const datosAsociacion = {
                estudiante_id: 1 // Asumiendo que existe un estudiante con ID 1
            };
            
            showData('Datos de asociación', datosAsociacion);
            addTestResult(`Probando POST /api/padres/${testPadreId}/asociar...`, 'info');
            
            const result = await apiRequest(`/api/padres/${testPadreId}/asociar`, {
                method: 'POST',
                body: JSON.stringify(datosAsociacion)
            });
            
            if (result.success) {
                addTestResult(`✅ POST /api/padres/${testPadreId}/asociar exitoso`, 'success');
                showResponse(result.data);
            } else {
                addTestResult(`❌ POST /api/padres/${testPadreId}/asociar falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        async function testDesasociarEstudiante() {
            if (!testPadreId) {
                addTestResult('❌ No hay ID de padre para probar. Ejecute primero "Crear Padre"', 'error');
                return;
            }
            
            const datosDesasociacion = {
                estudiante_id: 1
            };
            
            showData('Datos de desasociación', datosDesasociacion);
            addTestResult(`Probando DELETE /api/padres/${testPadreId}/desasociar...`, 'info');
            
            const result = await apiRequest(`/api/padres/${testPadreId}/desasociar`, {
                method: 'DELETE',
                body: JSON.stringify(datosDesasociacion)
            });
            
            if (result.success) {
                addTestResult(`✅ DELETE /api/padres/${testPadreId}/desasociar exitoso`, 'success');
                showResponse(result.data);
            } else {
                addTestResult(`❌ DELETE /api/padres/${testPadreId}/desasociar falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        async function testDeletePadre() {
            if (!testPadreId) {
                addTestResult('❌ No hay ID de padre para probar. Ejecute primero "Crear Padre"', 'error');
                return;
            }
            
            if (!confirm(`¿Está seguro de eliminar el padre con ID ${testPadreId}?`)) {
                addTestResult('❌ Eliminación cancelada por el usuario', 'error');
                return;
            }
            
            addTestResult(`Probando DELETE /api/padres/${testPadreId}...`, 'info');
            
            const result = await apiRequest(`/api/padres/${testPadreId}`, {
                method: 'DELETE'
            });
            
            if (result.success) {
                addTestResult(`✅ DELETE /api/padres/${testPadreId} exitoso`, 'success');
                showResponse(result.data);
                testPadreId = null; // Limpiar el ID ya que el padre fue eliminado
            } else {
                addTestResult(`❌ DELETE /api/padres/${testPadreId} falló - ${result.data.error}`, 'error');
                showResponse(result.data);
            }
        }
        
        // Cargar datos iniciales
        document.addEventListener('DOMContentLoaded', function() {
            addTestResult('🚀 Sistema de pruebas de API REST iniciado', 'info');
            addTestResult('💡 Recomendación: Ejecute las pruebas en orden: GET → POST → GET by ID → PUT → DELETE', 'info');
        });
    </script>
</body>
</html>
