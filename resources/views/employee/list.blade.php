@extends('layouts.app')
@section('title', 'Employee List')
@section('content')
<div class="content-wrapper">
     <section class="content-header">
        <h1>
            Employee
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Employee List</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        @if (Session::has('message'))
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
                        <form action="{{ route('employee-list') }}" method="get" class="form-horizontal">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <div class="col-sm-4 {{ !empty($errors->first('type')) ? 'has-error' : '' }}">
                                            <label for="type" class="control-label">Employee Type : </label>
                                            <select class="form-control" name="type" id="type" tabindex="3" style="width: 100%">
                                                <option value="" {{ (empty($type) || (empty(old('type')) && $type == 0)) ? 'selected' : '' }}>Select transaction type</option>
                                                <option value="1" {{ (!empty($type) && ((old('type') == 1 ) || $type == 1)) ? 'selected' : '' }}>Staff</option>
                                                <option value="2" {{ (!empty($type) && (old('type') == 2 || $type == 2)) ? 'selected' : '' }}>Labour</option>
                                            </select>
                                            @if(!empty($errors->first('type')))
                                                <p style="color: red;" >{{$errors->first('type')}}</p>
                                            @endif
                                        </div>
                                        <div class="col-sm-4     {{ !empty($errors->first('employee_id')) ? 'has-error' : '' }}">
                                            <label for="employee_id" class="control-label">Employee Name : </label>
                                            <select class="form-control" name="employee_id" id="employee_id" tabindex="3" style="width: 100%">
                                                @if(!empty($employeesCombobox) && (count($employeesCombobox) > 0))
                                                    <option value="">Select employee</option>
                                                    @foreach($employeesCombobox as $employee)
                                                        <option value="{{ $employee->id }}" {{ ((old('employee_id') == $employee->id ) || (!empty($employeeId) && $employeeId == $employee->id)) ? 'selected' : '' }}>{{ $employee->account->accountDetail->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(!empty($errors->first('employee_id')))
                                                <p style="color: red;" >{{$errors->first('employee_id')}}</p>
                                            @endif
                                        </div>
                                        <div class="col-sm-4 {{ !empty($errors->first('account_id')) ? 'has-error' : '' }}">
                                            <label for="account_id" class="control-label">Account : </label>
                                            <select class="form-control" name="account_id" id="account_id" tabindex="3" style="width: 100%">
                                                @if(!empty($accounts) && (count($accounts) > 0))
                                                    <option value="">Select employee account</option>
                                                    @foreach($accounts as $account)
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
                            <div class="row">
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
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Type</th>
                                            <th>Account Name</th>
                                            <th>Salary</th>
                                            <th>Wage</th>
                                            @if($currentUser->role == '1')
                                                <th class=" no-print">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($employees))
                                            @foreach($employees as $index => $employee)
                                                <tr>
                                                    <td>{{ $index + $employees->firstItem() }}</td>
                                                    <td>{{ $employee->account->accountDetail->name }}</td>
                                                    <td>{{ $employee->account->accountDetail->phone }}</td>
                                                    <td>{{ $employee->employee_type }}</td>
                                                    <td>{{ $employee->account->account_name }}</td>
                                                    @if($employee->employee_type == 1)
                                                        <td>{{ $employee->salary }}</td>
                                                        <td>-</td>
                                                    @elseif($employee->employee_type == 2)
                                                        <td>-</td>
                                                        <td>{{ $employee->wage }}</td>
                                                    @else
                                                        <td>-</td>
                                                        <td>-</td>
                                                    @endif
                                                    @if($currentUser->role == '1')
                                                        <td class=" no-print">
                                                            <form action="{{route('employee-edit')}}" id="employee_edit_{{ $index }}" method="get">
                                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                                <button type="submit" class="bg-aqua submit-button" type="button">Edit</button>
                                                            </form>
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
                                        @if(!empty($employees))
                                            {{ $employees->appends(Request::all())->links() }}
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
    <script src="/js/list/employee.js?rndstr={{ rand(1000,9999) }}"></script>
@endsection