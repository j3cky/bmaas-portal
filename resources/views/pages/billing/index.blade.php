@extends('layouts.homepage')

@stack('before-style')
@include('includes.style')
@stack('after-style')

@section('content')
    <div class="col-md-10" style="float:none;margin:auto;">
        <div class="cointainer">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-two-invoice-tab" data-toggle="pill"
                                href="#custom-tabs-two-invoice" role="tab" aria-controls="custom-tabs-two-invoice"
                                aria-selected="true">Invoice</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                href="#custom-tabs-two-profile" role="tab" aria-controls="custom-tabs-two-profile"
                                aria-selected="false">Payment Method</a>
                        </li>
                    </ul>
                </div>
                <div class="cointainer">
                    <div class="tab-content" id="custom-tabs-two-tabContent">
                        <div class="tab-pane fade show active" id="custom-tabs-two-invoice" role="tabpanel"
                            aria-labelledby="custom-tabs-two-invoice-tab">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Date</th>
                                            <th>Expiry Date</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><a href=""><u>Baremetal-202007-554963</u></a></td>
                                            <td><span>Overdue</span></td>
                                            <td>Rp539.354,84</td>
                                            <td>10 Jul 2020</td>
                                            <td>17 Jul 2020</td>
                                            <td><button type="button"
                                                    class="btn btn-block btn-outline-primary btn-sm">Pay</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Baremetal-202007-554963</td>
                                            <td><span>Overdue</span></td>
                                            <td>Rp539.354,84</td>
                                            <td>10 Jul 2020</td>
                                            <td>17 Jul 2020</td>
                                            <td><button type="button"
                                                    class="btn btn-block btn-outline-primary btn-sm">Pay</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Baremetal-202007-554963</td>
                                            <td><span>Overdue</span></td>
                                            <td>Rp539.354,84</td>
                                            <td>10 Jul 2020</td>
                                            <td>17 Jul 2020</td>
                                            <td><button type="button"
                                                    class="btn btn-block btn-outline-primary btn-sm">Pay</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-two-profile" role="tabpanel"
                            aria-labelledby="custom-tabs-two-profile-tab">
                            <div class="card-body" style="padding: 50px 70px 50px">
                                <div class="col-12 col-md-12">
                                    <div class="row">
                                        <div class="col-sm-6 text-center">
                                            <div class="col text-center">
                                                <div class="text-title">Current Balance</div>
                                                <div class="text-saldo">Rp10.000</div>
                                                <a><button type="submit" class="button btn-outline-primary btn-md">Top
                                                        Up</button></a>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 text-center">
                                            <div class="col text-center">
                                                <div class="text-title">Payment Information</div>
                                                <div class="text-saldo">No credit card added</div>
                                                <a><button type="submit" class="button btn-outline-primary btn-md">ADD CREDIT CARD</button></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@endsection
