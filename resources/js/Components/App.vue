<script setup>
    import axios from 'axios';
    import { ref, onMounted, computed } from 'vue';

    const startDate = new Date(2024, 8, 1);
    const endDate = new Date(2024, 8, 15);
    const zoom = ref(1);

    const dateList =  ref([]);
    const numbers = ref([]);

    const ws = ref([]);
    const ws_turno = ref([]);

    //formulario
    const form = ref({
        ID: null,
        WS_ID: null,
        DIASEM: null,
        INICIO: null,
        FIN: null,
        TIPO_RESP: null,
        CVE_RESP: null,
        CVE_DEP: null,
    });

    const form_add_ws = ref({
        WS_ID: null,
        DIASEM: null,
        INICIO: null,
        FIN: null,
        TIPO_RESP: null,
        CVE_RESP: null,
        CVE_DEP: null,
    });

    const formWs = ref({
        NOMBRE: null,
        QUICKLOG: null,
        SUBCONTRACT: null,
        CVE_VEND: null,
    });

    const vend20 = ref([]); 
    const deps = ref([]);

    const getWS = () => {
        axios
            .get('/ws')
            .then( (res)=> {
                ws.value = res.data.data;
            }).catch( err => {
                console.error(err);
            });
    }

    const getVend20 = () => {
        axios
            .get('/getVend')
            .then( (res)=> {
                vend20.value = res.data.data;
            }).catch( err => {
                console.error(err);
            });
    }

    const getDep = () => {
        axios
            .get('/getDep')
            .then( (res)=> {
                deps.value = res.data.data;
            }).catch( err => {
                console.error(err);
            });
    }

    const getWsTurno = () => {
        axios
            .get('/ws_turno')
            .then( (res)=> {
                ws_turno.value = res.data.data;
            }).catch( err => {
                console.error(err);
            });
    }

    const generateDateList = () => {
        const _dateList = [];
        let currentDate = startDate;
        while (currentDate <= endDate) {
            _dateList.push(new Date(currentDate));
            currentDate.setDate(currentDate.getDate() + 1);
        }
        dateList.value = _dateList;
    }

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}/${month}/${day}`;
    }

    const numbersCreate = (step = 1) => {
        numbers.value = [];
        zoom.value = step;

        for(let i = 0; i < 24; i += step ){
            numbers.value.push(i);
        }
    }

    const checkDay = (diasem, date) => {
        let dias = {
            1: 'Sun',
            2: 'Mon',
            3: 'Tue',
            4: 'Wed',
            5: 'Thu',
            6: 'Fri',
            7: 'Sat',
        }
        
        if(dias[diasem] == date.toDateString().slice(0,3)) return true;
        return false;
    };

    const getData = (id_row) => {
        axios
            .get('/getWsTurno/' + id_row)
            .then( (res) => {
                const data = res.data.data;
                form.value = {
                    ID: data.ID,
                    WS_ID: data.WS_ID,
                    DIASEM: data.DIASEM,
                    INICIO: data.INICIO.slice(0,2),
                    FIN: data.FIN.slice(0,2),
                    TIPO_RESP: data.TIPO_RESP,
                    CVE_RESP: data.CVE_RESP,
                    CVE_DEP: data.CVE_DEP,
                };
                if(data.CVE_RESP){
                    $('#cve_dep').attr('disabled', true);
                    $('#cve_resp').attr('disabled', false);
                }
                if(data.CVE_DEP){
                    $('#cve_dep').attr('disabled', false);
                    $('#cve_resp').attr('disabled', true);
                }
                
                $('#buttonModalEdit').click();               
            })
    }

    const editarTurno = () => {
        axios
            .post('/ws_turno_post', form.value)
            .then((res) => {
                getWS();
                getWsTurno();
                $('#buttonModalEdit').click();
            }).catch(err => alert(err.response.data.message));
    }

    const crearTurno = () => {
        axios
            .post('/ws_turno_add_post', form_add_ws.value)
            .then((res) => {
                getWS();
                getWsTurno();
                $('#ganttModalButton').click();
            }).catch(err => alert(err.response.data.message));
    }

    const cambioTipo = () => {
        if(form.TIPO_RESP == 'P'){
            $('#cve_dep').attr('disabled', true);
            $('#cve_resp').attr('disabled', false);
            form.value.CVE_DEP = null;
        }else {
            $('#cve_resp').attr('disabled', true);
            $('#cve_dep').attr('disabled', false);
            form.value.CVE_RESP = null;
        }
    }

    const addWS = () => {
        $('#wsModalButton').click();           
    }

    const openGantNew = () => {
        $('#ganttModalButton').click();
    }

    const createWs = () => {
        axios
            .post('/ws_post', formWs.value)
            .then((res) => {
                getWS();
                getWsTurno();
                $('#wsModalButton').click();
            }).catch(err => alert(err.response.data.message));
    }

    onMounted( () => {
        generateDateList();
        numbersCreate();
        getWS();
        getWsTurno();
        getVend20();
        getDep();
    });

    

</script>

<template>
    <div class="container">
        <div class="row" >
            <div class="col-3">
                <p>Centros de trabajo / Workstation</p>
                <div class="row">
                    <button class="btn btn-success" @click="numbersCreate(zoom + 1)">ZOOM +</button>
                    <button class="btn btn-warning" :disabled="zoom <= 1" @click="numbersCreate(zoom - 1)">ZOOM -</button>
                </div>

                <ol class="list-group list-group-numbered" style="margin-top: 1.8rem;">
                    <li v-for="w of ws" class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold">{{ w.ID }}</div>
                            {{ w.NOMBRE }}
                        </div>
                    </li>
                </ol>
                <div class="row my-3">
                    <button class="btn btn-primary" @click="addWS()">Agregar WS</button>
                    <button class="btn btn-primary mt-2" @click="openGantNew()">Agregar nuevo al Gantt</button>
                </div>
            </div>
            <div class="col-9" style="overflow: scroll;">
                <div class="container" style="height: 7.5rem;">
                    <div class="row">
                        <div class="col-auto m-auto">
                            <p class="text-center">SEPTIEMBRE</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <table class="">
                            <tr> <!-- FILA DE DIA EN EL MES -->
                                <td v-for="(date, index_i) in dateList" :key="date.toDateString()" class="table-cell">
                                    {{ formatDate(date) }}
                                    <table>
                                        <tr> <!-- FILA DE LA HORA -->
                                            <template v-for="hour in numbers" :key="hour">
                                                <td class="table-cell">
                                                    {{ hour }}:00
                                                </td>
                                            </template>
                                        </tr> <!-- END FILA DE LA HORA -->
                                    </table>                                    
                                </td>
                            </tr> <!-- END FILA DE DIA EN EL MES -->
                        </table>
                    </div>
      
                    <table style="margin-top: 2rem;">
                        <tr v-for="(data, id) in ws_turno" :key="id">
                            <td 
                                style="height: 4rem;"
                                class="table-cell"
                                v-for="(date, index_i) in dateList" :key="date.toDateString()"
                            >
                                <table>
                                    <tr>
                                        <template v-for="(turno, dia) in data" :key="dia">
                                            <template v-if="checkDay(dia, date)">
                                                <template v-for="hour in numbers" :key="hour">
                                                    <td
                                                        v-if="turno.horas[hour]"
                                                        class="table-cell active"
                                                        @click="getData(turno.horas[hour])"
                                                    >
                                                        Ocupado
                                                    </td>
                                                    <td v-else class="table-cell">
                                                        Libre
                                                    </td>
                                                </template>
                                            </template>
                                        </template>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>    


    <button id="buttonModalEdit" type="button" style="display: none;" data-bs-toggle="modal" data-bs-target="#editTurno">
        Launch demo modal
    </button>
    <!-- Modal -->
    <div class="modal fade" id="editTurno" tabindex="-1" aria-labelledby="labelTurno" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="labelTurno">WS TURNO EDIT</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <label class="form-label">ID</label>
                        <input type="text" v-model="form.ID" class="form-control" readonly>
                    </div>
                    <div class="col-6">
                        <label class="form-label">DIASEM</label>
                        <select class="form-select" v-model="form.DIASEM">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">HORA INICIO (24 Horas)</label>
                        <div class="d-flex">
                            <input v-model="form.INICIO" type="number" id="hour" class="form-control" min="0" max="23" step="1" placeholder="00">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">HORA FIN (24 Horas)</label>
                        <div class="d-flex">
                            <input v-model="form.FIN" type="number" id="hour" class="form-control" min="0" max="23" step="1" placeholder="00">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">TIPO RESP</label>
                        <select class="form-select" v-model="form.TIPO_RESP" @change="cambioTipo()">
                            <option value="P">P</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">CVE RESP</label>
                        <select id="cve_resp" class="form-select" v-model="form.CVE_RESP">
                            <option v-for="vend in vend20" :value="vend.CVE_VEND">{{ vend.NOMBRE }}</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">CVE DEP</label>
                        <select id="cve_dep" class="form-select" v-model="form.CVE_DEP">
                            <option v-for="dep in deps" :value="dep.CVE_DEP">{{ dep.NOMBRE }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" @click="editarTurno()">Guardar</button>
            </div>
            </div>
        </div>
    </div>

    <button id="ganttModalButton" type="button" style="display: none;" data-bs-toggle="modal" data-bs-target="#newWSturno">
        Launch demo modal
    </button>
    <!-- Modal -->
    <div class="modal fade" id="newWSturno" tabindex="-1" aria-labelledby="labelTurno" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="labelTurno">WS TURNO EDIT</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <label class="form-label">WS ID</label>
                        <select class="form-select" v-model="form_add_ws.WS_ID">
                            <option v-for="w of ws"  :value="w.ID">{{ w.NOMBRE }}</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">DIASEM</label>
                        <select class="form-select" v-model="form_add_ws.DIASEM">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">HORA INICIO (24 Horas)</label>
                        <div class="d-flex">
                            <input v-model="form_add_ws.INICIO" type="number" id="hour" class="form-control" min="0" max="23" step="1" placeholder="00">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">HORA FIN (24 Horas)</label>
                        <div class="d-flex">
                            <input v-model="form_add_ws.FIN" type="number" id="hour" class="form-control" min="0" max="23" step="1" placeholder="00">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">TIPO RESP</label>
                        <select class="form-select" v-model="form_add_ws.TIPO_RESP" @change="cambioTipo()">
                            <option value="P">P</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">CVE RESP</label>
                        <select id="cve_resp" class="form-select" v-model="form_add_ws.CVE_RESP">
                            <option v-for="vend in vend20" :value="vend.CVE_VEND">{{ vend.NOMBRE }}</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">CVE DEP</label>
                        <select id="cve_dep" class="form-select" v-model="form_add_ws.CVE_DEP">
                            <option v-for="dep in deps" :value="dep.CVE_DEP">{{ dep.NOMBRE }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" @click="crearTurno()">Guardar</button>
            </div>
            </div>
        </div>
    </div>


    <button id="wsModalButton" type="button" style="display: none;" data-bs-toggle="modal" data-bs-target="#wsModal">
        Launch demo modal
    </button>
    <!-- Modal -->
    <div class="modal fade" id="wsModal" tabindex="-1" aria-labelledby="labelTurno" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="labelTurno">AGREGAR WS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <label class="form-label">NOMBRE</label>
                        <input type="text" v-model="formWs.NOMBRE" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">QUICKLOG</label>
                        <select class="form-select" v-model="formWs.QUICKLOG">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">SUBCONTRACT</label>
                        <select class="form-select" v-model="formWs.SUBCONTRACT">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">CVE VEND</label>
                        <select class="form-select" v-model="formWs.CVE_VEND">
                            <option v-for="vend in vend20" :value="vend.CVE_VEND">{{ vend.NOMBRE }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" @click="createWs()">Guardar</button>
            </div>
            </div>
        </div>
    </div>


</template>

<style scoped>
    table {
        border-collapse: collapse;
    }
    .table-cell {
        border: 1px solid gray;
        padding: 5px;
        min-width: 80px !important  ;
        text-align: center;
    }

    .active {
        background-color: red;
        color: white;
    }
</style>