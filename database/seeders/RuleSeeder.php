<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run() :  void
    {
        $rules_array = [
            [
                'title' => 'Expense - Pro Tracker 1 Account 1 Credit Card',
                'type' => 1,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => null,
                'merge_fields' => null,
                'rule_fields' => [3],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Income - Pro Tracker 1 Account 1 Credit Card',
                'type' => 1,
                'user_id' => 1,
                'transaction_type' => 2,
                'category_id' => null,
                'merge_fields' => null,
                'rule_fields' => [3],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "inc",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Category Buffer - Pro Tracker 1 Account 1 Credit Card',
                'type' => 2,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => 4,
                'merge_fields' => null,
                'rule_fields' => [3],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Category Food - Pro Tracker 1 Account 1 Credit Card',
                'type' => 2,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => 1,
                'merge_fields' => null,
                'rule_fields' => [6, 3],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "trsaction-description",
                        "data" => [
                            "operator" => "contains",
                            "settings" => [
                                "text" => [
                                    "KAUFLAND",
                                    "FANTASTICO",
                                    "SUSHU",
                                    "FARMACY FARMAGROUP",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Category Taxes and Bills - Pro Tracker 1 Account 1 Credit Card',
                'type' => 2,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => 2,
                'merge_fields' => null,
                'rule_fields' => [3, 6],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "trsaction-description",
                        "data" => [
                            "operator" => "contains",
                            "settings" => [
                                "text" => [
                                    "WATER BILL",
                                    "ELECTRICITY BILL",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Category Gasoline - Pro Tracker 1 Account 1 Credit Card',
                'type' => 2,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => 3,
                'merge_fields' => null,
                'rule_fields' => [3, 6],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "trsaction-description",
                        "data" => [
                            "operator" => "contains",
                            "settings" => [
                                "text" => [
                                    "SHELL",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Category Credit - Pro Tracker 1 Account 1 Credit Card',
                'type' => 2,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => 5,
                'merge_fields' => null,
                'rule_fields' => [3, 6],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "trsaction-description",
                        "data" => [
                            "operator" => "contains",
                            "settings" => [
                                "text" => [
                                    "PAYMENT OF A HOUSING LOAN",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Category Vacation - Pro Tracker 1 Account 1 Credit Card',
                'type' => 2,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => 6,
                'merge_fields' => null,
                'rule_fields' => [3, 6],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "equals",
                            "settings" => [
                                "text" => [
                                    "exp",
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "trsaction-description",
                        "data" => [
                            "operator" => "contains",
                            "settings" => [
                                "text" => [
                                    "BOOKING HOTEL",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            [
                'title' => 'Merge Description Fields - Pro Tracker 1 Account 1 Credit Card',
                'type' => 3,
                'user_id' => 1,
                'transaction_type' => 1,
                'category_id' => null,
                'merge_fields' => ["Trsaction Description", "Additional Description"],
                'rule_fields' => [3],
                'rules' => [
                    [
                        "type" => "type",
                        "data" => [
                            "operator" => "contains.inverse",
                            "settings" => [
                                "text" => [
                                    "Always true condition",
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ];

        foreach ($rules_array as $rule) {
            $tmp_rule_array = $rule;
            unset($tmp_rule_array['rule_fields']);
            $ruleObject = Rule::create($tmp_rule_array);
            $ruleObject->rule_fields()->attach($rule['rule_fields']);
        }
    }
}
