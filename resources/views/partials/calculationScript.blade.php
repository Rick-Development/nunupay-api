@php
    $isHomePage = request()->is('/');
    $isTransferAmount = request()->routeIs('user.transferAmount');
    $isTransferRecipient = request()->routeIs('user.transferRecipient');
    $isTransferReview = request()->routeIs('user.transferReview');
    $isTransferPay = request()->routeIs('user.transferPay');
@endphp

@if($isTransferPay)
    <script>
        $(document).ready(function () {
            const sendAmount = {{ $payDetails->send_amount }};
            const receiverRate = {{ $payDetails->rate }};
            const senderCurrency = "{{ $payDetails->sender_currency }}";
            const receiverCurrency = "{{ $payDetails->receiver_currency }}";
            const transferFee = "{{ $payDetails->fees }}";
            const transferId = "{{ $payDetails->id }}";
            $('#supported_currency').select2();

            let selectedGateway = "";

            $(document).on('click', '.selectPayment', function () {
                $('.feedback').empty();

                if ($(this).val() == 0) {
                    if (!$('#supported_wallet').length) {
                        $(".currency").hide();
                        $('.showCharge').empty();
                        $('.selectWallet').append(
                            `<label class="form-label mt-0" for="supported_wallet">{{ trans('Select Your Wallet') }}
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                  data-bs-title="Please designate the wallet to which you wish to allocate payment.">
                            <i class="fa-regular fa-circle-question"></i></span>
                        </label>
                        <select class="cmn-select2 " name="wallet_id" id="supported_wallet">
                        @foreach($wallets as $item)
                            <option value="{{ $item->currency->code }}"
                                data-id="{{ $item->id }}">{{ $item->currency->code }} - {{ $item->currency->name }}
                            </option>
                        @endforeach
                            </select>`
                        );
                        $('[data-bs-toggle="tooltip"]').tooltip();
                        $('#supported_wallet').select2();
                        $('#supported_wallet').trigger('change');
                    }
                    $('#supported_wallet').trigger('change');
                    vm.gatewayId = $(this).val();
                } else {
                    $(".currency").show();
                    $('.selectWallet').empty();
                    selectedGateway = $(this).data('payment');
                    vm.gatewayId = selectedGateway;
                    supportCurrency(selectedGateway);
                }
            });


            $(document).on('change', '#supported_wallet', function () {
                const selectedCurrency = $(this).val();
                axios.get('{{ route('user.currencyRate') }}', {
                    params: {
                        selectedCurrency: selectedCurrency,
                        senderCurrency: senderCurrency
                    }
                })
                    .then(({data: {rate}}) => {
                        if (rate === null || rate === 0) {
                            $('.submitBtn').prop('disabled', true);
                            $('.showCharge').html('<ul class="list-group"><li class="list-group-item text-danger">{{ trans('Currency not found or rate is 0') }}</li></ul>');
                        } else {
                            const amount = (sendAmount * parseFloat(rate)).toFixed(2);

                            const selectedOption = $(this).find('option:selected');
                            const walletId = selectedOption.data('id');
                            let walletBalance = '';

                            axios.get('{{ route('user.walletBalance') }}', {
                                params: {
                                    walletId: walletId
                                }
                            })
                                .then(response => {
                                    walletBalance = response.data.walletBalance;
                                    vm.payAmount = amount;
                                    vm.supportedCurrency = selectedCurrency;

                                    if (!isNaN(amount) && amount > 0 && parseFloat(amount) < parseFloat(walletBalance)) {
                                        showCharge(amount, selectedCurrency);
                                        $('.submitBtn').prop('disabled', false);
                                        $('.feedback').empty();
                                    } else {
                                        $('.submitBtn').prop('disabled', true);
                                        $('.showCharge').html('');
                                        $('.feedback').text('Insufficient balance');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching wallet balance:', error);
                                });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching currency rates:', error);
                        Notiflix.Notify.failure('Error fetching currency rates. Please try again.');
                    });
            });

            function supportCurrency(selectedGateway) {
                if (!selectedGateway) {
                    console.error('Selected Gateway is undefined or null.');
                    return;
                }
                $.ajax({
                    url: "{{ route('supported.currency') }}",
                    data: {gateway: selectedGateway},
                    type: "GET",
                    success: function (response) {
                        const $supportedCurrency = $('#supported_currency').empty();
                        if (!response.data || response.data.length === 0) {
                            $supportedCurrency.append('<option value="" selected disabled>{{ trans('Supported Currency Not Found') }}</option>');
                        } else {
                            const currencies = response.currencyType == 1 ? response.data : ['USD'];
                            currencies.forEach(currency => $supportedCurrency.append(`<option value="${currency}">${currency}</option>`));
                            $supportedCurrency.trigger('change');
                        }

                        if (response.currencyType == 0) {
                            let $supportedCryptoCurrency = $('#supported_crypto_currency');
                            if ($supportedCryptoCurrency.length == 0) {
                                let markup2 = `
                                <label class="form-label" for="supported_crypto_currency">{{ trans('Pay To Crypto Currency') }}</label>
                                <select class="cmn-select2 form-control"
                                        name="supported_crypto_currency"
                                        id="supported_crypto_currency">
                                    <option value="">{{ trans('Select a Crypto Currency') }}</option>
                                </select>`;
                                $('.add-select-field').append(markup2);
                                $supportedCryptoCurrency = $('#supported_crypto_currency').select2();
                            } else {
                                $supportedCryptoCurrency.empty();
                                $supportedCryptoCurrency.append('<option value="">{{ trans('Select a Crypto Currency') }}</option>');
                            }

                            response.data.forEach(function (value) {
                                let markupOption = `<option value="${value}">${value}</option>`;
                                $supportedCryptoCurrency.append(markupOption);
                            });
                        }
                        else {
                            $('.add-select-field').empty();
                        }
                    },
                    error: function (error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }

            $(document).on('change', '#supported_currency, #supported_crypto_currency', function () {
                const selectedCurrency = $('#supported_currency').val();
                const selectedCryptoCurrency = $('#supported_crypto_currency').val();

                const pmcurrency = selectedCryptoCurrency ? selectedCryptoCurrency : selectedCurrency;

                axios.get('{{ route('user.currencyRate') }}', {
                    params: {
                        selectedCurrency: pmcurrency,
                        senderCurrency: senderCurrency
                    }
                })
                    .then(({data: {rate}}) => {

                        if (rate === null || rate === 0) {
                            $('.submitBtn').prop('disabled', true);

                            $('.showCharge').html('<ul class="list-group"><li class="list-group-item text-danger">{{ trans('Currency not found or rate is 0') }}</li></ul>');
                        } else {
                            const amount = sendAmount * parseFloat(rate);

                            vm.payAmount = amount;
                            vm.supportedCurrency = selectedCurrency;
                            vm.supportedCryptoCurrency = selectedCryptoCurrency;

                            if (!isNaN(amount) && amount > 0) {
                                checkAmount(amount, selectedCurrency, selectedGateway, selectedCryptoCurrency);
                                $('.submitBtn').prop('disabled', false);
                            } else {
                                $('.submitBtn').prop('disabled', true);
                                $('.showCharge').html('');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching currency rates:', error);
                        Notiflix.Notify.failure('Error fetching currency rates. Please try again.')
                    });
            });

            function checkAmount(amount, selectedCurrency, selectGateway, selectedCryptoCurrency = null) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('deposit.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'selected_currency': selectedCurrency,
                        'select_gateway': selectGateway,
                        'selectedCryptoCurrency': selectedCryptoCurrency,
                    }
                }).done(function (response) {
                    if (response.status) {
                        $('.feedback').html('');
                        amountStatus = true;
                        let amount = response.amount
                        let currency = response.currency
                        let base_currency = "{{basicControl()->base_currency}}"
                        showCharge(amount, currency);
                    } else {
                        amountStatus = false;
                        $('.submitBtn').prop('disabled', true);
                        $('.feedback').html(response.message);
                        $('.showCharge').html('');
                    }
                });
            }

            function showCharge(amount, currency) {

                let txnDetails = `<ul class="list-group">
						<li class="list-group-item d-flex justify-content-between py-4">
							<span class="fw-semibold">{{ __('Amount In') }} ${currency} </span>
							<span class="text-success fw-semibold"> ${amount} ${currency}</span>
						</li>
					</ul>`;
                $('.showCharge').html(txnDetails)
            }

            $(document).on('click', '#goNext', function () {
                let button = $(this);
                let amount = parseFloat(vm.payAmount);
                let gatewayId = vm.gatewayId;
                let supportedCurrency = vm.supportedCurrency;
                let supportedCryptoCurrency = vm.supportedCryptoCurrency;
                const selectedOption = $('#supported_wallet').find('option:selected');
                const walletId = selectedOption.data('id');

                let $url = '{{ route("payment.request", ["transfer" => "transfer:id"]) }}';
                $url = $url.replace('transfer:id', transferId);

                button.prop('disabled', true);
                Notiflix.Block.standard('#goNext', 'Processing...', {
                    backgroundColor: '#fff',
                });

                axios.post($url, {
                    amount: amount,
                    gateway_id: gatewayId,
                    supported_currency: supportedCurrency,
                    supported_crypto_currency: supportedCryptoCurrency,
                    wallet_id: walletId,
                })
                    .then(response => {
                        if (response.data.status == "success") {
                            window.location.href = response.data.url;
                        } else {
                            Notiflix.Notify.failure('Something wrong with transaction, please try again.')
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    }).finally(() => {
                    Notiflix.Block.remove('#goNext');
                });
            });

            let vm = new Vue({
                el: '#app',
                data: {
                    send_amount: sendAmount,
                    rate: receiverRate,
                    senderCurrency: senderCurrency,
                    receiverCurrency: receiverCurrency,
                    transferFee: transferFee,
                    sendTotal: 0,
                    get_amount: 0,
                    payAmount: 0,
                    gatewayId: null,
                    gatewayName: null,
                    supportedCurrency: null,
                    supportedCryptoCurrency: null,
                },
                mounted() {
                    this.getValue();
                },
                methods: {
                    getValue() {
                        this.sendTotal = (this.send_amount - transferFee).toFixed(2);
                        this.get_amount = (this.sendTotal * this.rate).toFixed(2);
                    }
                }
            });
        });
    </script>
@endif


@if($isTransferReview)
    <script>
        $(document).ready(function () {
            const sendFrom = JSON.parse(localStorage.getItem('sendFrom'));
            const receiveFrom = JSON.parse(localStorage.getItem('receiveFrom'));
            const {receiver_rate, send_amount, transferLocalFee, senderCurrency, receiverCurrency} = localStorage;

            const recipient_id = {{ $recipient->id }};
            let recipientServiceId = {{  $recipient?->service_id ?? 'null' }} ;
            let recipientUserId = {{  $recipient?->r_user_id ?? 'null' }} ;

            let vm = new Vue({
                el: '#app',
                data: {
                    sendAmount: send_amount,
                    rate: receiver_rate,
                    senderCurrency: senderCurrency,
                    receiverCurrency: receiverCurrency,
                    transferFee: parseFloat(transferLocalFee).toFixed(3),
                    sendFrom: sendFrom,
                    receiveFrom: receiveFrom,
                    get_amount: 0,
                    sendTotal: 0,
                    payTotal: 0,
                },
                mounted() {
                    this.getValue();
                },
                methods: {
                    getValue() {
                        this.payTotal = (parseFloat(this.sendAmount) + parseFloat(this.transferFee)).toFixed(2);
                        this.sendTotal = (this.sendAmount - this.transferFee).toFixed(2);
                        this.get_amount = (this.sendTotal * this.rate).toFixed(2);
                    },
                    goNext() {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        localStorage.setItem('recipientId', recipient_id);
                        const parameter = {
                            recipient_id,
                            r_user_id: recipientUserId,
                            send_currency_id: this.sendFrom.id,
                            receive_currency_id: this.receiveFrom.id,
                            service_id: recipientServiceId,
                            sender_currency: senderCurrency,
                            receiver_currency: receiverCurrency,
                            recipient_get_amount: this.get_amount,
                            rate: this.rate,
                            send_amount: this.sendAmount,
                            fees: this.transferFee,
                            payable_amount: this.sendTotal,
                        };

                        axios.post('{{ route('user.paymentStore') }}', parameter)
                            .then(response => {
                                window.location.href = response.data.paymentUrl;
                            })
                            .catch(error => {
                                 console.log(error)

                                const failedUrl = "{{ route('user.transferAmount') }}";
                                const errorMessage = error.response.data.message;
                                const customError = error.response.data.error;
                                console.log(errorMessage)
                                setTimeout(() => {
                                    window.location.href = failedUrl;
                                }, 2500);

                                if (errorMessage || customError) {
                                    Notiflix.Notify.failure(errorMessage || customError);
                                }
                            });
                    }
                }
            });
        });
    </script>
@endif


@if($isTransferAmount || $isHomePage)

    <script>
        new Vue({
            el: "#MoneyTransfer",
            data: {
                minTransferFee: {{ basicControl()->min_transfer_fee }},
                maxTransferFee: {{ basicControl()->max_transfer_fee }},
                minAmount: {{ basicControl()->min_amount }},
                maxAmount: {{ basicControl()->max_amount }},
                transferFee: 0,
                transferLocalFee: 0,
                totalAmount: 0,
                senderCurrencyRate: '',
                senderCurrencies: [],
                receiverCurrencies: [],
                sendFrom: {},
                receiveFrom: {},
                send_amount: '',
                get_amount: '',
                rate: '',
            },
            beforeMount() {
                this.currencyList();
            },
            mounted() {
                let self = this;
            },
            methods: {
                getCookie(name) {
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) return parts.pop().split(';').shift();
                },
                currencyList() {
                    axios.get('{{route('currencyList')}}')
                        .then(res => {
                            this.senderCurrencies = res.data.senderCurrencies;
                            this.receiverCurrencies = res.data.receiverCurrencies;

                            const senderCurrency = this.getCookie('senderCurrency');
                            const receiverCurrency = this.getCookie('receiverCurrency');

                            this.sendFrom = this.senderCurrencies.find(item => item.code === senderCurrency) || this.senderCurrencies[0] || {};
                            this.receiveFrom = this.receiverCurrencies.find(item => item.code === receiverCurrency) || this.receiverCurrencies[0] || {};

                            this.getRate();
                        })
                        .catch(err => {
                            console.error(err);
                        });
                },

                changeSender(id) {
                    $("#senderModal").modal("hide");

                    let self = this;
                    let arr = self.senderCurrencies;
                    const result = arr.find((obj, index) => {
                        if (obj.id == id) {
                            return true
                        }
                    });
                    this.sendFrom = result

                    this.getRate();
                    this.getValue();
                },

                changeReceive(id) {
                    $("#receiverModal").modal("hide");

                    let self = this;
                    let arr = self.receiverCurrencies;
                    const result = arr.find((obj, index) => {
                        if (obj.id == id) {
                            return true
                        }
                    });
                    this.receiveFrom = result

                    this.getRate()
                    this.getValue();
                },
                onlyNumber(event) {
                    !/[\d.]/.test(event.key) && event.preventDefault();
                },
                updateSenderAmount() {
                    this.getRate();
                    this.getValue();
                    this.send_amount = this.send_amount.replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1');
                },
                updateRecipientAmount() {
                    this.getRate();
                    this.sendValue();
                    this.get_amount = this.get_amount.replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1');
                },
                getRate() {
                    if (this.receiveFrom.rate && this.sendFrom.rate) {
                        this.rate = (this.receiveFrom.rate / this.sendFrom.rate).toFixed(10);

                        const isSameCurrency = this.sendFrom.code === "USD";
                        const sendAmountInUSD = isSameCurrency
                            ? parseFloat(this.send_amount)
                            : parseFloat(this.send_amount) / this.sendFrom.rate;

                        let percentageIncrease = 0;

                        if (sendAmountInUSD > 5000) {
                            this.transferFee = this.maxTransferFee;
                        } else if (sendAmountInUSD > 2000) {
                            percentageIncrease = 1;
                            this.transferFee = this.minTransferFee * (1 + percentageIncrease);
                        } else {
                            this.transferFee = this.minTransferFee;
                        }

                        const transferFeeInTargetCurrency = isSameCurrency
                            ? this.transferFee
                            : this.transferFee * (this.sendFrom.rate);

                        const amount = parseFloat(this.send_amount) || 0;

                        this.transferLocalFee = transferFeeInTargetCurrency;
                        const sendLocalAmount = parseFloat((amount - this.transferLocalFee).toFixed(3));

                        this.totalAmount = parseFloat((sendLocalAmount * this.rate).toFixed(3));
                        if (this.totalAmount < 0 ) {
                            this.totalAmount = 0;
                        }

                        this.senderCurrencyRate = this.sendFrom.rate;
                    } else {
                        this.rate = 0;
                        this.transferLocalFee = 0;
                        this.totalAmount = 0;
                    }
                },
                getValue() {
                    this.get_amount = this.send_amount && this.rate ? Math.abs((this.send_amount * this.rate).toFixed(2)) : '';
                },
                sendValue() {
                    this.send_amount = this.get_amount && this.rate ? Math.abs((this.get_amount / this.rate).toFixed(2)) : '';
                },
                goNext() {
                    const sendAmountUSD = this.send_amount / this.senderCurrencyRate;

                    if (sendAmountUSD < this.minAmount || sendAmountUSD > this.maxAmount) {
                        Notiflix.Notify.failure('Send amount must be between ' + this.minAmount + ' USD and ' + this.maxAmount + ' USD');
                    } else {
                        axios.get('{{ route('user.clearSession') }}');

                        let $url = '{{ route("user.transferRecipient", ["country" => "country:name"]) }}';
                        $url = $url.replace('country:name', this.receiveFrom.country.name);

                        localStorage.setItem('send_amount', this.send_amount);
                        localStorage.setItem('receiver_rate', this.rate);
                        localStorage.setItem('senderCurrency', this.sendFrom.code);
                        localStorage.setItem('receiverCurrency', this.receiveFrom.code);
                        localStorage.setItem('transferLocalFee', this.transferLocalFee);
                        localStorage.setItem('sendSelectId', this.sendFrom.id);
                        localStorage.setItem('sendSelectFlag', this.sendFrom.image);
                        localStorage.setItem('sendSelectName', this.sendFrom.name);
                        localStorage.setItem('sendSelectCode', this.sendFrom.code);

                        localStorage.setItem('sendFrom', JSON.stringify(this.sendFrom));
                        localStorage.setItem('receiveFrom', JSON.stringify(this.receiveFrom));
                        localStorage.setItem('resource', JSON.stringify(this.sendFrom));

                        const expirationDate = new Date();
                        expirationDate.setFullYear(expirationDate.getFullYear() + 1);
                        document.cookie = `senderCurrency=${this.sendFrom.code}; expires=${expirationDate.toUTCString()}; path=/`;
                        document.cookie = `receiverCurrency=${this.receiveFrom.code}; expires=${expirationDate.toUTCString()}; path=/`;

                        window.location.href = $url
                    }
                }
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function handleInput(inputAmountBox, inputAmountBoxInner) {
                const inputField = inputAmountBoxInner.querySelector('input');

                inputField.addEventListener("focus", function () {
                    inputAmountBox.classList.add("active");
                });

                inputField.addEventListener("blur", function () {
                    inputAmountBox.classList.remove("active");
                });

                inputField.addEventListener("click", function () {
                    inputField.setSelectionRange(inputField.value.length, inputField.value.length);
                });

                inputAmountBox.addEventListener("click", function (event) {
                    if (!event.target.closest('.icon-area') && !event.target.closest('.currency-name')) {
                        inputField.focus();
                        inputField.setSelectionRange(inputField.value.length, inputField.value.length);
                    }
                });
            }

            const inputAmountBox = document.getElementById("inputAmountBox");
            const inputAmountBoxInner = document.getElementById("inputAmountBoxInner");
            handleInput(inputAmountBox, inputAmountBoxInner);

            const inputAmountBox2 = document.getElementById("inputAmountBox2");
            const inputAmountBoxInner2 = document.getElementById("inputAmountBoxInner2");
            handleInput(inputAmountBox2, inputAmountBoxInner2);
        });

        function filterItems(inputId) {
            var input, filter, items, title, subtitle, i, txtValue;
            input = document.getElementById(inputId);
            filter = input.value.toUpperCase();
            items = document.querySelectorAll("#currency-list .item");

            items.forEach(function (item) {
                title = item.querySelector(".title");
                subtitle = item.querySelector(".sub-title");

                txtValue = title.textContent || title.innerText;
                txtValue += " " + (subtitle.textContent || subtitle.innerText);

                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }
    </script>

@endif
