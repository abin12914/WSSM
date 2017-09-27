@extends('layouts.app')
@section('title', 'Account List')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Account
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Account List</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        @if(Session::has('message'))
            <div class="alert {{ Session::get('alert-class', 'alert-info') }}" id="alert-message">
                <h4>
                  {!! Session::get('message') !!}
                  <?php session()->forget('message'); ?>
                </h4>
            </div>
        @endif
        <!-- Main row -->
        <div class="row no-print">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Filter List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-header">
                        <form action="{{ route('account-list') }}" method="get" class="form-horizontal">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="col-sm-4 {{ !empty($errors->first('type')) ? 'has-error' : '' }}">
                                            <label for="type" class="control-label">Type : </label>
                                            <select class="form-control" name="type" id="type" tabindex="3" style="width: 100%">
                                                <option value="" {{ (empty($type) || (empty(old('type')) && $type == 0)) ? 'selected' : '' }}>Select account type</option>
                                                <option value="1" {{ (!empty($type) && ((old('type') == '1' ) || $type == '1')) ? 'selected' : '' }}>Real Account</option>
                                                <option value="2" {{ (!empty($type) && (old('type') == '2' || $type == '2')) ? 'selected' : '' }}>Nominal</option>
                                                <option value="3" {{ (!empty($type) && (old('type') == '3' || $type == '3')) ? 'selected' : '' }}>Personal</option>
                                            </select>
                                            @if(!empty($errors->first('type')))
                                                <p style="color: red;" >{{$errors->first('type')}}</p>
                                            @endif
                                        </div>
                                        <div class="col-sm-4 {{ !empty($errors->first('relation')) ? 'has-error' : '' }}">
                                            <label for="relation" class="control-label">Relation : </label>
                                            <select class="form-control" name="relation" id="relation" tabindex="3" style="width: 100%">
                                                <option value="" {{ (empty($relation) || (empty(old('relation')) && $relation == 0)) ? 'selected' : '' }}>Select relation type</option>
                                                <option value="1" {{ (!empty($relation) && ((old('relation') == '1' ) || $relation == '1')) ? 'selected' : '' }}>Employee</option>
                                                <option value="2" {{ (!empty($relation) && (old('relation') == '2' || $relation == '2')) ? 'selected' : '' }}>Supplier</option>
                                                <option value="3" {{ (!empty($relation) && (old('relation') == '3' || $relation == '3')) ? 'selected' : '' }}>Customer</option>
                                                <option value="4" {{ (!empty($relation) && (old('relation') == '4' || $relation == '4')) ? 'selected' : '' }}>Contractor</option>
                                                <option value="5" {{ (!empty($relation) && (old('relation') == '5' || $relation == '5')) ? 'selected' : '' }}>General</option>
                                            </select>
                                            @if(!empty($errors->first('relation')))
                                                <p style="color: red;" >{{$errors->first('relation')}}</p>
                                            @endif
                                        </div>
                                        <div class="col-sm-4     {{ !empty($errors->first('account_id')) ? 'has-error' : '' }}">
                                            <label for="account_id" class="control-label">Account : </label>
                                            <select class="form-control" name="account_id" id="account_id" tabindex="3" style="width: 100%">
                                                @if(!empty($accountsCombobox) && (count($accountsCombobox) > 0))
                                                    <option value="">Select account</option>
                                                    @foreach($accountsCombobox as $account)
                                                        <option value="{{ $account->id }}" {{ ((old('account_id') == $account->id ) || (!empty($accountId) && $accountId == $account->id)) ? 'selected' : '' }}>{{ $account->account_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(!empty($errors->first('account_id')))
                                                <p style="color: red;" >{{$errors->first('account_id')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div><br>
                            <div class="row no-print">
                                <div class="col-md-4"></div>
                                <div class="col-md-2">
                                    <button type="reset" class="btn btn-default btn-block btn-flat"  value="reset" tabindex="10">Clear</button>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat submit-button" tabindex="4"><i class="fa fa-search"></i> Search</button>
                                </div>
                            </div>
                        </form>
                        <!-- /.form end -->
                    </div><br>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Account Name</th>
                                            <th>Type</th>
                                            <th>Relation</th>
                                            <th>Account Holder/Head</th>
                                            <th>Opening Credit</th>
                                            <th>Opening Debit</th>
                                            @if($currentUser->role == 1)
                                                <th class=" no-print">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($accounts))
                                            @foreach($accounts as $index => $account)
                                                <tr>
                                                    <td>{{ $index + $accounts->firstItem() }}</td>
                                                    <td>{{ $account->account_name }}</td>
                                                    @if($account->type == 1)
                                                        <td>Real Account</td>
                                                    @elseif($account->type == 2)
                                                        <td>Nominal Account</td>
                                                    @else
                                                        <td>Personal Account</td>
                                                    @endif
                                                    <td>{{ $account->relation }}</td>
                                                    <td>{{ $account->accountDetail->name }}</td>
                                                    @if($account->financial_status == 'debit')
                                                        <td>0</td>
                                                        <td>{{ $account->opening_balance }}</td>
                                                    @elseif($account->financial_status == 'credit')
                                                        <td>{{ $account->opening_balance }}</td>
                                                        <td>0</td>
                                                    @else
                                                        <td>0</td>
                                                        <td>0</td>
                                                    @endif
                                                    @if($currentUser->role == 1)
                                                        <td class=" no-print">
                                                            @if($account->type == 3)
                                                                <form action="{{route('account-edit')}}" id="account_edit_{{ $index }}" method="get">
                                                                <input type="hidden" name="account_id" value="{{ $account->id }}">
                                                                <button type="submit" class="bg-aqua submit-button" type="button">Edit</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row no-print">
                            <div class="col-md-12">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <div class="pull-right">
                                        @if(!empty($accounts))
                                            {{ $accounts->appends(Request::all())->links() }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.boxy -->
            </div>
            <!-- /.col-md-12 -->
        </div>
        <!-- /.row (main row) -->
    </section>
    <!-- /.content -->
</div>
@endsection
@section('scripts')
    <script src="/js/list/account.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection