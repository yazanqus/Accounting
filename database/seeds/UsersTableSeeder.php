<?php

use App\User;
use App\Utility;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrPermissions = [
            'show dashboard',
            'manage user',
            'create user',
            'edit user',
            'delete user',
            'create language',
            'manage system settings',
            'manage role',
            'create role',
            'edit role',
            'delete role',
            'manage permission',
            'create permission',
            'edit permission',
            'delete permission',
            'manage company settings',
            'manage business settings',
            'manage stripe settings',
            'manage expense',
            'create expense',
            'edit expense',
            'delete expense',
            'manage invoice',
            'create invoice',
            'edit invoice',
            'delete invoice',
            'show invoice',
            'create payment invoice',
            'delete payment invoice',
            'send invoice',
            'delete invoice product',
            'convert invoice',
            'manage plan',
            'create plan',
            'edit plan',
            'manage constant unit',
            'create constant unit',
            'edit constant unit',
            'delete constant unit',
            'manage constant tax',
            'create constant tax',
            'edit constant tax',
            'delete constant tax',
            'manage constant category',
            'create constant category',
            'edit constant category',
            'delete constant category',
            'manage product & service',
            'create product & service',
            'edit product & service',
            'delete product & service',
            'manage customer',
            'create customer',
            'edit customer',
            'delete customer',
            'show customer',
            'manage vender',
            'create vender',
            'edit vender',
            'delete vender',
            'show vender',
            'manage bank account',
            'create bank account',
            'edit bank account',
            'delete bank account',
            'manage transfer',
            'create transfer',
            'edit transfer',
            'delete transfer',
            'manage constant payment method',
            'create constant payment method',
            'edit constant payment method',
            'delete constant payment method',
            'manage transaction',
            'manage revenue',
            'create revenue',
            'edit revenue',
            'delete revenue',
            'manage bill',
            'create bill',
            'edit bill',
            'delete bill',
            'show bill',
            'manage payment',
            'create payment',
            'edit payment',
            'delete payment',
            'delete bill product',
            'buy plan',
            'send bill',
            'create payment bill',
            'delete payment bill',
            'manage order',
            'income report',
            'expense report',
            'income vs expense report',
            'invoice report',
            'bill report',
            'tax report',
            'loss & profit report',
            'manage customer payment',
            'manage customer transaction',
            'manage customer invoice',
            'vender manage bill',
            'manage vender bill',
            'manage vender payment',
            'manage vender transaction',
            'manage credit note',
            'create credit note',
            'edit credit note',
            'delete credit note',
            'manage debit note',
            'create debit note',
            'edit debit note',
            'delete debit note',
            'duplicate invoice',
            'duplicate bill',
            'manage coupon',
            'create coupon',
            'edit coupon',
            'delete coupon',
            'manage proposal',
            'create proposal',
            'edit proposal',
            'delete proposal',
            'duplicate proposal',
            'show proposal',
            'send proposal',
            'delete proposal product',
            'manage customer proposal',
            'manage goal',
            'create goal',
            'edit goal',
            'delete goal',
            'manage assets',
            'create assets',
            'edit assets',
            'delete assets',
            'statement report',
            'manage constant custom field',
            'create constant custom field',
            'edit constant custom field',
            'delete constant custom field',
            'manage chart of account',
            'create chart of account',
            'edit chart of account',
            'delete chart of account',
            'manage journal entry',
            'create journal entry',
            'edit journal entry',
            'delete journal entry',
            'show journal entry',
            'balance sheet report',
            'ledger report',
            'trial balance report',
        ];

        foreach($arrPermissions as $ap)
        {
            Permission::create(['name' => $ap]);
        }

        // Super admin

        $superAdminRole        = Role::create(
            [
                'name' => 'super admin',
                'created_by' => 0,
            ]
        );
        $superAdminPermissions = [
            'manage user',
            'create user',
            'edit user',
            'delete user',
            'create language',
            'manage system settings',
            'manage stripe settings',
            'manage role',
            'create role',
            'edit role',
            'delete role',
            'manage permission',
            'create permission',
            'edit permission',
            'delete permission',
            'manage plan',
            'create plan',
            'edit plan',
            'manage order',
            'manage coupon',
            'create coupon',
            'edit coupon',
            'delete coupon',

        ];
        foreach($superAdminPermissions as $ap)
        {
            $permission = Permission::findByName($ap);
            $superAdminRole->givePermissionTo($permission);
        }
        $superAdmin = User::create(
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('1234'),
                'type' => 'super admin',
                'lang' => 'en',
                'avatar' => '',
                'created_by' => 0,
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // customer
        $customerRole       = Role::create(
            [
                'name' => 'customer',
                'created_by' => 0,
            ]
        );
        $customerPermission = [
            'manage customer payment',
            'manage customer transaction',
            'manage customer invoice',
            'show invoice',
            'show proposal',
            'manage customer proposal',
            'show customer',
        ];

        foreach($customerPermission as $ap)
        {
            $permission = Permission::findByName($ap);
            $customerRole->givePermissionTo($permission);
        }

        // vender
        $venderRole       = Role::create(
            [
                'name' => 'vender',
                'created_by' => 0,
            ]
        );
        $venderPermission = [
            'vender manage bill',
            'manage vender bill',
            'manage vender payment',
            'manage vender transaction',
            'show vender',
            'show bill',
        ];

        foreach($venderPermission as $ap)
        {
            $permission = Permission::findByName($ap);
            $venderRole->givePermissionTo($permission);
        }


        // company

        $companyRole        = Role::create(
            [
                'name' => 'company',
                'created_by' => $superAdmin->id,
            ]
        );
        $companyPermissions = [
            'show dashboard',
            'manage user',
            'create user',
            'edit user',
            'delete user',
            'manage role',
            'create role',
            'edit role',
            'delete role',
            'manage permission',
            'create permission',
            'edit permission',
            'delete permission',
            'manage company settings',
            'manage business settings',
            'manage expense',
            'create expense',
            'edit expense',
            'delete expense',
            'manage invoice',
            'create invoice',
            'edit invoice',
            'delete invoice',
            'show invoice',
            'manage plan',
            'buy plan',
            'manage product & service',
            'create product & service',
            'delete product & service',
            'edit product & service',
            'manage constant tax',
            'create constant tax',
            'edit constant tax',
            'delete constant tax',
            'manage constant category',
            'create constant category',
            'edit constant category',
            'delete constant category',
            'manage constant unit',
            'create constant unit',
            'edit constant unit',
            'delete constant unit',
            'manage customer',
            'create customer',
            'edit customer',
            'delete customer',
            'show customer',
            'manage vender',
            'create vender',
            'edit vender',
            'delete vender',
            'show vender',
            'manage bank account',
            'create bank account',
            'edit bank account',
            'delete bank account',
            'manage transfer',
            'create transfer',
            'edit transfer',
            'delete transfer',
            'manage revenue',
            'create revenue',
            'edit revenue',
            'delete revenue',
            'manage bill',
            'create bill',
            'edit bill',
            'delete bill',
            'show bill',
            'manage payment',
            'create payment',
            'edit payment',
            'delete payment',
            'delete invoice product',
            'delete bill product',
            'send invoice',
            'create payment invoice',
            'delete payment invoice',
            'send bill',
            'create payment bill',
            'delete payment bill',
            'income report',
            'expense report',
            'income vs expense report',
            'invoice report',
            'bill report',
            'tax report',
            'loss & profit report',
            'manage transaction',
            'manage order',
            'manage credit note',
            'create credit note',
            'edit credit note',
            'delete credit note',
            'manage debit note',
            'create debit note',
            'edit debit note',
            'delete debit note',
            'duplicate invoice',
            'convert invoice',
            'duplicate bill',
            'manage proposal',
            'create proposal',
            'edit proposal',
            'delete proposal',
            'duplicate proposal',
            'show proposal',
            'send proposal',
            'delete proposal product',
            'manage goal',
            'create goal',
            'edit goal',
            'delete goal',
            'manage assets',
            'create assets',
            'edit assets',
            'delete assets',
            'statement report',
            'manage constant custom field',
            'create constant custom field',
            'edit constant custom field',
            'delete constant custom field',
            'manage chart of account',
            'create chart of account',
            'edit chart of account',
            'delete chart of account',
            'manage journal entry',
            'create journal entry',
            'edit journal entry',
            'delete journal entry',
            'show journal entry',
            'balance sheet report',
            'ledger report',
            'trial balance report',
        ];

        foreach($companyPermissions as $ap)
        {
            $permission = Permission::findByName($ap);
            $companyRole->givePermissionTo($permission);
        }
        $company = User::create(
            [
                'name' => 'company',
                'email' => 'company@example.com',
                'password' => Hash::make('1234'),
                'type' => 'company',
                'lang' => 'en',
                'avatar' => '',
                'plan' => 1,
                'created_by' => $superAdmin->id,
            ]
        );
        $company->assignRole($companyRole);

        // accountant
        $accountantRole       = Role::create(
            [
                'name' => 'accountant',
                'created_by' => $company->id,
            ]
        );
        $accountantPermission = [
            'show dashboard',
            'manage expense',
            'create expense',
            'edit expense',
            'delete expense',
            'manage invoice',
            'create invoice',
            'edit invoice',
            'delete invoice',
            'show invoice',
            'convert invoice',
            'manage product & service',
            'create product & service',
            'delete product & service',
            'edit product & service',
            'manage constant tax',
            'create constant tax',
            'edit constant tax',
            'delete constant tax',
            'manage constant category',
            'create constant category',
            'edit constant category',
            'delete constant category',
            'manage constant unit',
            'create constant unit',
            'edit constant unit',
            'delete constant unit',
            'manage customer',
            'create customer',
            'edit customer',
            'delete customer',
            'show customer',
            'manage vender',
            'create vender',
            'edit vender',
            'delete vender',
            'show vender',
            'manage bank account',
            'create bank account',
            'edit bank account',
            'delete bank account',
            'manage transfer',
            'create transfer',
            'edit transfer',
            'delete transfer',
            'manage revenue',
            'create revenue',
            'edit revenue',
            'delete revenue',
            'manage bill',
            'create bill',
            'edit bill',
            'delete bill',
            'show bill',
            'manage payment',
            'create payment',
            'edit payment',
            'delete payment',
            'delete invoice product',
            'delete bill product',
            'send invoice',
            'create payment invoice',
            'delete payment invoice',
            'send bill',
            'create payment bill',
            'delete payment bill',
            'income report',
            'expense report',
            'income vs expense report',
            'invoice report',
            'bill report',
            'tax report',
            'loss & profit report',
            'manage transaction',
            'manage credit note',
            'create credit note',
            'edit credit note',
            'delete credit note',
            'manage debit note',
            'create debit note',
            'edit debit note',
            'delete debit note',
            'manage proposal',
            'create proposal',
            'edit proposal',
            'delete proposal',
            'duplicate proposal',
            'send proposal',
            'show proposal',
            'delete proposal product',
            'manage goal',
            'create goal',
            'edit goal',
            'delete goal',
            'manage assets',
            'create assets',
            'edit assets',
            'delete assets',
            'statement report',
            'manage constant custom field',
            'create constant custom field',
            'edit constant custom field',
            'delete constant custom field',
            'manage chart of account',
            'create chart of account',
            'edit chart of account',
            'delete chart of account',
            'manage journal entry',
            'create journal entry',
            'edit journal entry',
            'delete journal entry',
            'show journal entry',
            'balance sheet report',
            'ledger report',
            'trial balance report',
        ];

        foreach($accountantPermission as $ap)
        {
            $permission = Permission::findByName($ap);
            $accountantRole->givePermissionTo($permission);
        }

        $accountant = User::create(
            [
                'name' => 'accountant',
                'email' => 'accountant@example.com',
                'password' => Hash::make('1234'),
                'type' => 'accountant',
                'lang' => 'en',
                'avatar' => '',
                'created_by' => $company->id,
            ]
        );
        $accountant->assignRole($accountantRole);

        \App\BankAccount::create(
            [
                'holder_name' => 'Cash',
                'bank_name' => '',
                'account_number' => '-',
                'opening_balance' => '0.00',
                'contact_number' => '-',
                'bank_address' => '-',
                'created_by' => $company->id,
            ]
        );

        Utility::chartOfAccountTypeData();
        Utility::chartOfAccountData($company);
        Utility::add_landing_page_data();
    }
}
