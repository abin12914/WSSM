<!DOCTYPE html>
<html>
<head>
    <!-- sections/head.main.blade -->
    @include('sections.head')
    @section('stylesheets')
    @show
    <!-- iCheck -->
    <link rel="stylesheet" href="/css/plugins/iCheck/flat/green.css">
</head>
<body>
    <div class="wrapper">
        <!-- Content Wrapper. Contains page content -->
        {{-- <div class="content-wrapper"> --}}
        <!-- Main content -->
        <section class="content">
            @if(!empty($productCategories) && count($productCategories) > 0)
                @foreach($productCategories as $category)
                    <section>
                        <h4 class="page-header" style="color: red;">{{ $category->category_name }}</h4>
                        {{-- <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-5">
                                <select class="multi-select-combo" multiple="multiple" data-placeholder="Select products" style="width: 100%;">
                                    <option></option>
                                    @foreach($category->products as $productCombo)
                                        <option value="{{ $productCombo->id }}">{{ $productCombo->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        <div class="row">
                        @foreach($category->products as $k => $product)
                            <div class="col-md-3 col-sm-4">
                                {{-- <input type="checkbox" name="product_list[]" value="{{ $product->id }}">{{ $product->name }} --}}
                                {{-- <div class="input-group"> --}}
                                    {{-- <span class="input-group-addon"> --}}
                                        <input type="checkbox" class="flat-green" name="product_list[]" id="product_list_item_{{ $product->id }}" value="{{ $product->id }}">
                                    {{-- </span> --}}
                                    <label for="product_list_item_{{ $product->id }}" {{-- class="form-control" --}}>{{ $product->name }}</label>
                                {{-- </div> --}}
                            </div>
                        @endforeach
                        </div>
                    </section><br>
                @endforeach
            @else
                <h3 style="color: red;">No products available</h3>
            @endif
        </section>
        <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->
    @include('sections.scripts')
    <!-- iCheck 1.0.1 -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/js/main.js?rndstr={{ rand(1000,9999) }}"></script>
    <script type="text/javascript">
        //Flat red color scheme for iCheck
        $('.flat-green').iCheck({
          checkboxClass: 'icheckbox_flat-green',
        });
        /*$(".multi-select-combo").select2({
            multiple: true
        });*/
    </script>
</body>
</html>