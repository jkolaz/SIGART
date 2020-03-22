<section class="hk-sec-wrapper">
            <h6 class="hk-sec-title">Listado</h6>
            <div class="row">
                <div class="col-sm">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                	<th>Tipo comprobante</th>
                                    <th>Comprobante</th>
                                    <th>Proveedor</th>
                                    <th>Nro Doc</th>
                                    <th>Fecha de compra</th>
                                    <th>Fecha de pago</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{$purchase->typeVoucher}}</td>
                                    <td>{{$purchase->document}}</td>
                                    <td>{{$purchase->provider->name}}</td>
                                    <td>{{$purchase->provider->document}}</td>
                                    <td>{{$purchase->issue}}</td>
                                    <td>---</td>
                                    <td>{{$purchase->subTotal}}</td>
                                    <td>
                                        @if($purchase->status == 4)
                                        <span class="badge badge-primary">{{$purchase->statusName}}</span>
                                        @endif
                                        @if($purchase->status == 3)
                                        <span class="badge badge-orange">{{$purchase->statusName}}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                
                                
                                
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
                        <a class="page-link" href="#" >Ant.</a>
                    </li>
                    @for($page = $paginate['from']; $page<=$paginate['to']; $page++)
                    <li @if($paginate['current_page'] == $page)  class="page-item active " @else  class="page-item " @endif>
                        <a class="page-link" href="#">{{$page}}</a>
                    </li>
                    @endfor
                    <li class="page-item">
                        <a class="page-link" href="#">Sig.</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</section>