<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Faker\Factory as Faker;

class DemoContentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $user_count = 76;
        $company_count = 28;
        $project_count = 16;
        $invoice_count = 28;
        $invoice_start_number = 260;
        $invoice_item_count = [5, 12];
        $invoice_discount_count = [1, 1];

        $faker = Faker::create();

        // Users
        $managers = [];
        $manager_ids = [];
        $employees = [];
        $employee_ids = [];
        $contractors = [];
        $contractor_ids = [];
        $clients = [];
        $client_ids = [];
        $leads = [];
        $lead_ids = [];

        $men = 1;
        $women = 1;
        if ($user_count > 0) {
          foreach (range(1, $user_count - 1) as $index) {
            $gender = (mt_rand(0, 1) == 1) ? 'male' : 'female';
            if ($gender == 'male') {
              $firstName = $faker->firstNameMale;
              $avatar = new \SplFileInfo(base_path() . '/database/seeds/avatars/men/' . $men . '.jpg');
              $men++;
            } else {
              $firstName = $faker->firstNameFemale;
              $avatar = new \SplFileInfo(base_path() . '/database/seeds/avatars/women/' . $women . '.jpg');
              $women++;
            }
            $lastName = $faker->lastName;
            $email = str_slug(substr($firstName, 0, 1)) . '.' . str_slug($lastName, '_') . '@' . $faker->domainName;

            //$active = (mt_rand(0,4) == 0) ? false : true;
            $active = true;

            $created_at = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');
            $updated_at = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');

            DB::table('users')->insert([
              'account_id' => 1,
              'name' => $firstName . ' ' . $lastName,
              'first_name' => $firstName,
              'last_name' => $lastName,
              'salutation' => $faker->title($gender),
              'job_title' => substr($faker->jobTitle, 0, 63),
              'email' => $email,
              'password' => bcrypt('welcome'),
              'email_verified_at' => $faker->dateTimeThisYear($max = '-1 week'),
              'phone' => $faker->e164PhoneNumber,
              'street1' => $faker->streetAddress,
              'city' => $faker->city,
              'state' => $faker->state,
              'postal_code' => $faker->postcode,
              'timezone' => $faker->timezone,
              'currency_code' => $faker->currencyCode,
              'active' => $active,
              'signup_ip_address' => $faker->ipv4(),
              'logins' => $faker->numberBetween($min = 1, $max = 50),
              'last_login' => $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now'),
              'last_login_ip_address' => $faker->ipv4(),
              'created_at' => $created_at,
              'created_by' => 1,
              'updated_at' => $updated_at,
              'updated_by' => 1
            ]);

            $user = \App\User::find($index + 1);

            $user->avatar = $avatar;
            $user->save();

            // Assign role
            $role_dice = mt_rand(0, 12);
            if ($role_dice === 1 || $role_dice === 2) {
              $user->assignRole('Manager');
              $managers[] = $user;
              $manager_ids[] = $user->id;
            } elseif ($role_dice === 3 || $role_dice === 4 || $role_dice === 5) {
              $user->assignRole('Employee');
              $employees[] = $user;
              $employee_ids[] = $user->id;
            } elseif ($role_dice === 6 || $role_dice === 7 || $role_dice === 8) {
              $user->assignRole('Contractor');
              $contractors[] = $user;
              $contractor_ids[] = $user->id;
            } else {
              $user->assignRole('Client');
              $clients[] = $user;
              $client_ids[] = $user->id;
            }
          }
        }

        // Companies
        if ($company_count > 0) {
          foreach (range(1, $company_count) as $index) {
            $default = ($index == 1) ? true : false;
            $active = true;
            $company = $faker->company;
            $email_pre = ['info', 'info', 'info', 'contact', 'hi', 'hello'];
            $email_tld = ['com', 'com', 'com', 'org', 'biz', 'store', 'agency'];
            $email = $email_pre[mt_rand(0, count($email_pre) - 1)] . '@' . str_slug($company, '-') . '.' . $email_tld[mt_rand(0, count($email_tld) - 1)];

            $industries = ['Accounting & Legal', 'Advertising', 'Automotive', 'Banking & Finance', 'Business Services', 'Communications', 'Internet & Online', 'Entertainment', 'Marketing', 'Media', 'Transportation'];

            $created_at = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');
            $updated_at = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');

            DB::table('companies')->insert([
              'account_id' => 1,
              'default' => $default,
              'active' => $active,
              'name' => $company,
              'email' => $email,
              'industry' => $industries[mt_rand(0, count($industries) - 1)],
              'website' => $faker->domainName,
              'phone' => $faker->e164PhoneNumber,
              'street1' => $faker->streetAddress,
              'city' => $faker->city,
              'state' => $faker->state,
              'postal_code' => $faker->postcode,
              'short_description' => $faker->catchPhrase,
              'created_at' => $created_at,
              'created_by' => 1,
              'updated_at' => $updated_at,
              'updated_by' => 1
            ]);

            $company = \Platform\Models\Company::find($index);

            // Add client to company
            if (isset($clients[$index - 1])) {
              $clients[$index - 1]->companies()->sync($company->id);
              if ($clients[$index - 1]->active == false) {
                $company->active = false;
                $company->save();
              }
            }
          }
        }

        if ($project_count > 0) {
          foreach (range(1, $project_count) as $index) {
            $notify_people_involved = true;
            $active = true;
            $task_count = mt_rand(8, 26);
						$reference = 'PRJ-' . mt_rand(100,999) . '-' . strtoupper(str_random(2));

            $max_employees = (count($employee_ids) > 10) ? 10 : 8;
            $employee_pool = array_random($employee_ids, mt_rand(4, $max_employees));
            $max_contractors = (count($contractor_ids) > 5) ? 5 : 3;
            $contractor_pool = array_random($contractor_ids, mt_rand(2, 5));

            $project_status_id = [1, 28, 21, 32, 43, 45, 49, 72];
            $project_status_id = array_random($project_status_id);

            $created_at = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');
            $updated_at = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');

            $start_date = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');
            $due_date = $faker->dateTimeBetween($startDate = $start_date, $endDate = '+3 months');

            $completed_date = null;
            if ($project_status_id == 72) {
              $completed_date = $faker->dateTimeBetween($startDate = $start_date, $endDate = 'now');
            }

            DB::table('projects')->insert([
              'company_id' => mt_rand(1, $company_count - 1),
              'project_status_id' => $project_status_id,
              'active' => $active,
              'name' => ucfirst($faker->bs),
              'short_description' => $faker->catchPhrase,
              'description' => '<h1>' . $faker->realText($maxNbChars = 36, $indexSize = 1) . '</h1><p>' . implode('</p><p>', $faker->paragraphs($nb = 3, $asText = false)) . '</p><h2>' . $faker->realText($maxNbChars = 36, $indexSize = 1) . '</h2><p>' . implode('</p><p>', $faker->paragraphs($nb = 3, $asText = false)) . '</p>',
              'category' => $faker->bs,
              'reference' => $reference,
              'client_can_comment' => true,
              'client_can_view_tasks' => true,
              'client_can_view_description' => true,
              'client_can_upload_files' => true,
              'client_can_approve_proposition' => true,
              'notify_people_involved' => $notify_people_involved,
              'start_date' => $start_date,
              'due_date' => $due_date,
              'completed_date' => $completed_date,
              'currency_code' => 'USD',
              'created_at' => $created_at,
              'created_by' => 1,
              'updated_at' => $updated_at,
              'updated_by' => 1
            ]);

            $project = \Platform\Models\Project::find($index);

            // Add managers
            $project_managers = [];
            if (count($manager_ids) >= 2) {
              $project_managers = array_random($manager_ids, mt_rand(1,2));
              $project->managers()->sync($project_managers); 
            }

            // Add tasks
            for($i = 1; $i < $task_count; $i++) {
              $hours = [2, 5, 10, 12, 15, 25, 30, 35, 40];
              $hourly_rate = [25, 35, 40, 50, 75, 80, 90];
              $project_status_id = [1, 28, 21, 32, 43, 45, 49, 72];
              $project_status_id = array_random($project_status_id);
              $priority = [0,1,2,3,1,1,1,1,1,1,1];
              $priority = array_random($priority);

              $assigned_to_id = [];

              if (count($employee_pool) >= 2) {
                $assigned_to_id = array_random($employee_pool, mt_rand(1,2));
              }

              if (count($contractor_pool) >= 1) {
                $assigned_to_id = array_merge($assigned_to_id, array_random($contractor_pool, mt_rand(0,1)));
              }

              $created_at = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');
              $updated_at = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');

              $start_date = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');
              $due_date = $faker->dateTimeBetween($startDate = $start_date, $endDate = '+3 months');

              $completed_date = null;
              $completed_by_id = null;
              if ($project_status_id == 72) {
                $completed_date = $faker->dateTimeBetween($startDate = $start_date, $endDate = 'now');
                $completed_by_id = array_random($assigned_to_id);
              }

              $billable = (mt_rand(0,1) == 0) ? true : false;
              $hours = $hours[mt_rand(0, count($hours) - 1)];
              $hourly_rate = $hourly_rate[mt_rand(0, count($hourly_rate) - 1)];

              $project_task = new \Platform\Models\ProjectTask;
              $project_task->project_id = $project->id;
              $project_task->project_status_id = $project_status_id;
              $project_task->subject = ucfirst($faker->bs);
              $project_task->priority = $priority;
              $project_task->description = '<p>' . implode('</p><p>', $faker->paragraphs($nb = 3, $asText = false)) . '</p>';
              $project_task->start_date = $start_date;
              $project_task->due_date = $due_date;
              $project_task->billable = $billable;
              $project_task->hours = $hours * 100;
              $project_task->hourly_rate = $hourly_rate * 100;
              $project_task->due_date = $due_date;
              $project_task->completed_date = $completed_date;
              $project_task->completed_by_id = $completed_by_id;
              $project_task->save();

              // Sync assignees
              if (! empty($assigned_to_id)) {
                $project_task->assignees()->sync($assigned_to_id);
              }

              // Notify assignee(s)
              if ($notify_people_involved == 1 && ! empty($project_managers)) {
                if ($project_task->assignees->count() > 0) {
                  foreach ($project_task->assignees as $user) {
                    if ($user->active) {
                      \Notification::send($user, new \App\Notifications\ProjectAssignedToTask(env('APP_URL') . '/login', \App\User::find(array_random($project_managers)), $user, $project_task));
                    }
                  }
                }
              }
            }

						// Add proposition
						$tax_rates = [1900, 2000, 2100];

						$items = [
							['description' => 'Planning, research / outreach', 'quantity' => [15, 20, 10], 'unit' => 'hour', 'unit' => 'hour', 'unit_price' => [7000, 8000, 9000]],
							['description' => 'Design, consultation', 'quantity' => [32, 35, 38], 'unit' => 'hour', 'unit' => 'hour', 'unit_price' => [7200, 7500, 7000]],
							['description' => 'Development', 'quantity' => [60, 70, 80], 'unit' => 'hour', 'unit' => 'hour', 'unit_price' => [7000, 8000, 9000]],
							['description' => 'Testing, launch', 'quantity' => [15, 20, 10], 'unit' => 'hour', 'unit' => 'hour', 'unit_price' => [4000, 4500, 4800]]
						];

						$discounts = [
							['description' => 'Loyal customer discount', 'quantity' => [10, 15, 20], 'discount_type' => '%']
						];

						// Totals
						$total = 0;
						$total_discount = 0;
						$total_tax = 0;

						// Random tax rate
						$tax_rate = $tax_rates[mt_rand(0, count($tax_rates) - 1)];
						$tax = $tax_rate / 100;

						// Proposition items
						$proposition_items = [];
						foreach ($items as $item) {
							$quantity = $item['quantity'][mt_rand(0, count($item['quantity']) - 1)];
							$unit_price = $item['unit_price'][mt_rand(0, count($item['unit_price']) - 1)];
							$total_without_tax = $quantity * $unit_price;

							$total += $total_without_tax;
							$total_tax += ($total_without_tax * $tax) / 100;

							$proposition_items[] = ['type' => 'item', 'description' => $item['description'], 'quantity' => $quantity, 'unit' => $item['unit'], 'unit_price' => $unit_price, 'discount_type' => null];
						}

						$total_items = $total;

						foreach ($discounts as $item) {
							$quantity = $item['quantity'][mt_rand(0, count($item['quantity']) - 1)];
							if ($item['discount_type'] == '%') {
								$total_without_tax = ($total_items / 100) * $quantity;
							} else {
								$total_without_tax = $quantity;
							}

							$total -= $total_without_tax;
							$total_tax -= ($total_without_tax * $tax) / 100;
							$total_discount += $total_without_tax;

							$proposition_items[] = ['type' => 'discount', 'description' => $item['description'], 'quantity' => $quantity, 'discount_type' => $item['discount_type'], 'unit' => null, 'unit_price' => null];
						}

						$total_tax = round($total_tax);

						$created_at = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');
						$updated_at = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');

						$project_proposition = new \Platform\Models\ProjectProposition;
						$project_proposition->account_id = 1;
						$project_proposition->project_id = $project->id;
						$project_proposition->reference = $reference;
						$project_proposition->locked = 0;
						$project_proposition->total = $total;
						$project_proposition->total_discount = $total_discount;
						$project_proposition->total_tax = $total_tax;
						$project_proposition->proposition_valid_until = $faker->dateTimeBetween($startDate = '+1 months', $endDate = '+2 months');
						$project_proposition->created_by = 1;
						$project_proposition->created_at = $created_at;
						$project_proposition->updated_by = 1;
						$project_proposition->updated_at = $updated_at;
						$project_proposition->save();

						// Items
						foreach ($proposition_items as $item) {
							$project_proposition_item = new \Platform\Models\ProjectPropositionItem;
							$project_proposition_item->project_proposition_id = $project_proposition->id;
							$project_proposition_item->type = $item['type'];
							$project_proposition_item->description = $item['description'];
							$project_proposition_item->quantity = $item['quantity'] * 100;
							$project_proposition_item->unit = $item['unit'];
							$project_proposition_item->discount_type = $item['discount_type'];
							$project_proposition_item->unit_price = $item['unit_price'];
							$project_proposition_item->tax_rate = $tax_rate;
							$project_proposition_item->type = $item['type'];
							$project_proposition_item->created_by = 1;
							$project_proposition_item->created_at = $created_at;
							$project_proposition_item->updated_by = 1;
							$project_proposition_item->updated_at = $updated_at;
							$project_proposition_item->save();
						}
          }
        }

        if ($invoice_count > 0) {
          foreach (range(1, $invoice_count) as $invoice_index) {
						$reference = $invoice_start_number;
            $invoice_start_number++;

            $item_count = mt_rand($invoice_item_count[0], $invoice_item_count[1]);
            $discount_count = mt_rand($invoice_discount_count[0], $invoice_discount_count[1]);

						$tax_rates = [1900, 2000, 2100];

						// Totals
						$total = 0;
						$total_discount = 0;
						$total_tax = 0;

						// Random tax rate
						$tax_rate = $tax_rates[mt_rand(0, count($tax_rates) - 1)];
						$tax = $tax_rate / 100;

						// Invoice items
						$invoice_items = [];
            $invoice_item_quantity = [5, 12];
            $invoice_item_unit_price = [4000, 4500, 4800, 7000, 8000, 9000];
            $invoice_discount_quantity = [10, 15, 20];

						foreach (range(1, $item_count) as $index) {
							$quantity = mt_rand($invoice_item_quantity[0], $invoice_item_quantity[1]);
							$unit_price = $invoice_item_unit_price[mt_rand(0, count($invoice_item_unit_price) - 1)];
							$total_without_tax = $quantity * $unit_price;

							$total += $total_without_tax;
							$total_tax += ($total_without_tax * $tax) / 100;

							$invoice_items[] = [
                'type' => 'item', 
                'description' => ucfirst($faker->bs), 
                'quantity' => $quantity, 
                'unit' => 'hour', 
                'unit_price' => $unit_price, 
                'discount_type' => null
              ];
						}

						$total_items = $total;

						foreach (range(1, $discount_count) as $index) {
							$quantity = $invoice_discount_quantity[mt_rand(0, count($invoice_discount_quantity) - 1)];

              // Always use %
              $total_without_tax = ($total_items / 100) * $quantity;

							$total -= $total_without_tax;
							$total_tax -= ($total_without_tax * $tax) / 100;
							$total_discount += $total_without_tax;

							$invoice_items[] = [
                'type' => 'discount', 
                'description' => ucfirst($faker->bs), 
                'quantity' => $quantity, 
                'discount_type' => '%', 
                'unit' => null, 
                'unit_price' => null
              ];
						}

            $total_tax = round($total_tax);

            $days_ago = (($invoice_count * 2) - ($invoice_index * 2));

						$created_at = $faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now');
						$updated_at = $faker->dateTimeBetween($startDate = $created_at, $endDate = 'now');

            $issue_date = $faker->dateTimeBetween($startDate = '-' . $days_ago . ' days', $endDate = '-' . $days_ago . ' days');
            $due_date = $faker->dateTimeBetween($startDate = '-' . ($days_ago - 14) . ' days', $endDate = '-' . ($days_ago - 14) . ' days');

            $sent_date = null;
						$paid_date = null;
						$partially_paid_date = null;
						$written_off_date = null;

            // Only different status for last invoices
            if ($invoice_index >= $invoice_count - 2) {
              // Draft
            } elseif ($invoice_index >= $invoice_count - 5) {
              $sent_date = $faker->dateTimeBetween($startDate = '-' . $days_ago . ' days', $endDate = '-' . $days_ago . ' days');
            } else {
              $paid_within_days = mt_rand(5, 14);
              $sent_date = $faker->dateTimeBetween($startDate = '-' . $days_ago . ' days', $endDate = '-' . $days_ago . ' days');
              $paid_date = $faker->dateTimeBetween($startDate = '-' . ($days_ago - $paid_within_days) . ' days', $endDate = '-' . ($days_ago - $paid_within_days) . ' days');
            }

						$invoice = new \Platform\Models\Invoice;
						$invoice->account_id = 1;
						$invoice->company_id = mt_rand(2, $company_count - 1);
						$invoice->reference = $reference;
						$invoice->currency_code = 'USD';
						$invoice->total = $total;
						$invoice->total_discount = $total_discount;
						$invoice->total_tax = $total_tax;

						$invoice->issue_date = $issue_date;
						$invoice->due_date = $due_date;
						$invoice->sent_date = $sent_date;
						$invoice->paid_date = $paid_date;
						$invoice->partially_paid_date = $partially_paid_date;
						$invoice->written_off_date = $written_off_date;
  
						$invoice->created_by = 1;
						$invoice->created_at = $created_at;
						$invoice->updated_by = 1;
						$invoice->updated_at = $updated_at;
						$invoice->save();

						// Items
						foreach ($invoice_items as $item) {
							$invoice_item = new \Platform\Models\InvoiceItem;
							$invoice_item->invoice_id = $invoice->id;
							$invoice_item->type = $item['type'];
							$invoice_item->description = $item['description'];
							$invoice_item->quantity = $item['quantity'] * 100;
							$invoice_item->unit = $item['unit'];
							$invoice_item->discount_type = $item['discount_type'];
							$invoice_item->unit_price = $item['unit_price'];
							$invoice_item->tax_rate = $tax_rate;
							$invoice_item->type = $item['type'];
							$invoice_item->created_by = 1;
							$invoice_item->created_at = $created_at;
							$invoice_item->updated_by = 1;
							$invoice_item->updated_at = $updated_at;
							$invoice_item->save();
						}
          }
        }

        Eloquent::reguard();
    }
}
