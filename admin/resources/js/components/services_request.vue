<template>
    <div>
        <section class="hk-sec-wrapper">
            <h5 class="hk-sec-title">Solicitudes de Cotización</h5>
            <div class="row">
                <div class="col-sm">
                    <form class="form-inline">
                        <div class="form-row align-items-left">
                            <div class="col-auto">
                                <label class="sr-only" for="inlineFormInput">Name</label>
                                <input type="text" v-model="search" @keyup="list(1, search)" class="form-control mb-2" id="inlineFormInput" placeholder="Buscar...">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary mb-2" @click.prevent="list(1, search)">
                                    <i class="fa fa-fw fa-lg fa-search"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <section class="hk-sec-wrapper">
            <h6 class="hk-sec-title">Listado</h6>
            <div class="row">
                <div class="col-sm">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Código</th>
                                    <th v-if="tipo === 'derive'">Cotización</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Descripcion</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="dato in arreglo" :key="dato.id">
                                    <td><strong class="text-info">{{ dato.document }}</strong></td>
                                    <td v-if="dato.isDerive">
                                        <strong v-if="dato.saleQuotation.exist">{{ dato.saleQuotation.document }}</strong>
                                        <br/>
                                        <small v-if="dato.saleQuotation.exist">Total: <strong>S/ {{ dato.saleQuotation.total }}</strong></small>
                                        <br/>
                                        <g-status section="sale-quotation" :status="dato.saleQuotation.status"></g-status>
                                    </td>
                                    <td v-text="dato.send"></td>
                                    <td>
                                        {{ dato.customer.name }}
                                        <br>
                                        <span class="badge badge-info" v-text="dato.customer.document"></span>
                                    </td>
                                    <td v-text="dato.description"></td>
                                    <td>
                                        <div v-if="dato.isDerive">
                                            <span class="badge badge-success">Solicitud Derivada</span>
                                        </div>
                                        <div v-else>
                                            <span class="badge badge-danger">Solicitud Pendiente</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button v-if="!dato.isDerive" type="button"  class="btn btn-outline-success btn-xs" @click="derivarRequest(dato.id)">
                                            <i class="fa fa-check"></i> Derivar Solicitud
                                        </button>
                                        <button  type="button"  class="btn btn-outline-info btn-xs" @click="openDetailModal(dato)">
                                            <i class="fa fa-info"></i> Ver Detalle
                                        </button>
                                        <button v-if="dato.isDerive" type="button"  class="btn btn-outline-success btn-xs"  @click.prevent="redirect(dato.id)">
                                            <i class="fa fa-money"></i> Generar cotización
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="hk-sec-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="changePage( pagination.current_page-1 )">Ant.</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="changePage( page )" v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="changePage( pagination.current_page+1 )">Sig.</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </section>
        <b-modal id="modalDetail" size="lg" ref="modalDetail" :title="modalTitle" @ok="closeModal"  @modal-ok="true">
            <form @submit.stop.prevent="closeModal">
                <div class="row">
                    <div class="col-sm">
                        <div class="table-wrap">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>Descripción/th>
                                        <th>Cantidad</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="datadet in detailData" :key="datadet.id">
                                            <td v-text="datadet.description"></td>
                                            <td v-text="datadet.quantity"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </b-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    import datepicker from 'vue-bootstrap-datetimepicker';
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
    import GStatus from '../vuex/components/general/status';

    function getDate () {
        const toTwoDigits = num => num < 10 ? '0' + num : num;
        let today = new Date();
        let year = today.getFullYear();
        let month = toTwoDigits(today.getMonth() + 1);
        let day = toTwoDigits(today.getDate());
        return `${day}/${month}/${year}`;
    }

    export default {
        name: 'services-request-adm',
        components: {
            datepicker,
            GStatus
        },
        props: [
            'tipo'
        ],
        data(){
            return{
                id:             0,
                description : '',
                arreglo: [],
                detailData: [],
                modalTitle: '',
                modal: 0,
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                search : '',
                options: {
                    format: 'YYYY-MM-DD',
                    useCurrent: false,
                    showClear: true,
                    showClose: true,
                }
            }
        },
        computed:{
            isActived: function(){
                return this.pagination.current_page;
            },
            //Calcula los elementos de la paginación
            pagesNumber: function() {
                if(!this.pagination.to) {
                    return [];
                }

                var from = this.pagination.current_page - this.offset;
                if(from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2);
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }

                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;

            }
        },
        methods:{
            changeTabForm( tab ) {
                this.formTab = tab;
            },
            redirect(services){
                window.location = URL_PROJECT + '/service_request/list-materials/' + services;
            },
            customFormatter(date) {
                return moment(date).format('YYYY-MM-DD');
            },
            list(page,search){
                var me = this;
                var url= '/service_request?page=' + page + '&type=' + me.tipo +'&search='+ search;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arreglo = respuesta.records;
                    me.pagination= respuesta.pagination;
                })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            changePage( page ){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.list(page,this.search);
            },

            derivarRequest(id){
                swal({
                    title: "Derivar Solicitud!",
                    text: "Esta seguro de transferir esta Solicitud?",
                    icon: "success",
                    button: "Aceptar"
                }).then((result) => {
                    if (result) {
                        let me = this;

                        axios.put('/service_request/derive',{
                            'id': id
                        }).then(function (response) {
                            me.list(1,'','nombre');
                            swal(
                                'Transferido!',
                                'El registro ha sido transferido con éxito.',
                                'success'
                            )
                        }).catch(function (error) {
                            swal(
                                'Error! :(',
                                'No se pudo realizar la operación',
                                'error'
                            );
                        });


                    }
                })
            },
            getDetail( id ) {
                let me = this,
                    url = '/service_request/details';
                axios.get( url, {
                    params: {
                        'id': id
                    }
                }).then( function( result ) {
                    //console.log(result);
                    let response    = result.data;
                    console.log(response.records);
                    me.detailData   = response.records;

                });
            },
            desactivar(id){
                swal({
                    title: "Desactivar Usuario!",
                    text: "Esta seguro de desactivar este administrador?",
                    icon: "warning",
                    button: "Desactivar",
                }).then((result) => {
                    if (result) {
                        let me = this;

                        axios.put('/service_request/deactivate',{
                            'id': id
                        }).then(function (response) {
                            me.list(1,'','nombre');
                            swal(
                                'Desactivado!',
                                'El registro ha sido desactivado con éxito.',
                                'success'
                            )
                        }).catch(function (error) {
                            swal(
                                'Error! :(',
                                'No se pudo realizar la operación',
                                'error'
                            )
                        });


                    }
                })
            },
            openDetailModal( data ) {
                this.modalTitle = 'Detalle Solicitud de Contizacion - ' + data.description;
                this.getDetail( data.id );
                this.$refs.modalDetail.show();
            },
            closeModal() {
                this.modalTitle = '';
                this.detailData = [];
                this.detailId = 0;
                this.$nextTick(() => {
                    this.$refs.modalDetail.hide();
                })
            }
        },
        mounted() {
            this.list(1,this.search);
        }
    }
</script>
<style scoped>
    .jk-tab-pane {
        margin-top: 2vw;
    }
    .jk-tab-none {
        display: none;
    }
</style>
