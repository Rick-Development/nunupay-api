@extends($theme.'layouts.user')
@section('title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('section')
    <div class="dashboard-wrapper">
        <div class="col-xxl-8 col-lg-10 mx-auto">
        <div class="breadcrumb-area"><h3 class="title">@yield('title')</h3></div>
			<div class="row justify-content-center">
				<div class="col-md-5">
					<div class="card">
						<div class="card-body text-center">
							<form
								action="{{ route('ipn', [optional($deposit->gateway)->code ?? 'mercadopago', $deposit->trx_id]) }}"
								method="POST">
								<script src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
										data-preference-id="{{ $data->preference }}">
								</script>
							</form>
						</div>
					</div>
				</div>
			</div>
        </div>
    </div>
@endsection
