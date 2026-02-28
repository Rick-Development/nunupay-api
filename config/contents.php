<?php
return [
    'hero' => [
        'single' => [
            'field_name' => [
                'main_heading' => 'text',
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
                'sub_title' => 'text',
                'image' => 'file',
                'button_one' => 'text',
                'button_link_one' => 'url',
                'button_two' => 'text',
                'button_link_two' => 'url',
            ],
            'validation' => [
                'main_heading.*' => 'required|max:100',
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:200',
                'title.*' => 'required|max:100',
                'sub_title.*' => 'required|max:200',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png',
                'button_one.*' => 'required|max:100',
                'button_link_one.*' => 'nullable',
                'button_two.*' => 'required|max:100',
                'button_link_two.*' => 'nullable',
            ]
        ],
        'multiple' => [
            'field_name' => [
                'feature' => 'text',
            ],
            'validation' => [
                'feature.*' => 'required|max:100',
            ]
        ],
    ],

    'about' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'description' => 'textarea',
                'question' => 'text',
                'button_name' => 'text',
                'button_link' => 'url',
                'image' => 'file',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'description.*' => 'required',
                'question.*' => 'nullable',
                'button_name.*' => 'nullable',
                'button_link.*' => 'nullable',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png'
            ]
        ],
    ],

    'features' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'title.*' => 'required|max:200',
            ]
        ],
        'multiple' => [
            'field_name' => [
                'title' => 'text',
                'sub_title' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'title.*' => 'required|max:100',
                'sub_title.*' => 'required|max:200',
                'image.*' => 'required|max:3072|image|mimes:jpg,jpeg,png'
            ],
            'size' => [
                 'image' => '64x64'
            ]
        ],
    ],

    'how_it_work' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'title.*' => 'required|max:200',
            ]
        ],
        'multiple' => [
            'field_name' => [
                'title' => 'text',
                'sub_title' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'title.*' => 'required|max:100',
                'sub_title.*' => 'required|max:200',
                'image.*' => 'required|max:3072|image|mimes:jpg,jpeg,png'
            ],
            'size' => [
                 'image' => '65x65'
            ]
        ],
    ],

    'why_choose_us' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'description' => 'textarea',
                'image' => 'file',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'description.*' => 'required',
                'image.*' => 'nullable|max:3072|image|mimes:jpg,jpeg,png'
            ]
        ],
        'multiple' =>[
            'field_name' => [
                'step' => 'text',
            ],
            'validation' => [
                'step' => 'required|max:150',
            ]
        ],
    ],

    'testimonial' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'title.*' => 'required|max:200',
            ]
        ],
        'multiple' => [
            'field_name' => [
                'name' => 'text',
                'location' => 'text',
                'narration' => 'textarea',
                'image' => 'file',
            ],
            'validation' => [
                'name.*' => 'required|max:100',
                'location.*' => 'required|max:100',
                'narration.*' => 'required|max:2000',
                'image.*' => 'required|max:3072|image|mimes:jpg,jpeg,png'
            ],
            'size' => [
                'image' => '440x380'
            ]
        ],
    ],

    'news_letter' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'button_name' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'button_name.*' => 'required|max:200',
            ]
        ],

    ],

    'faq' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'title.*' => 'required|max:200',
                'image.*' => 'required|max:3072|image|mimes:jpg,jpeg,png'
            ]
        ],
        'multiple' => [
            'field_name' => [
                'question' => 'text',
                'answer' => 'textarea',
            ],
            'validation' => [
                'question.*' => 'required|max:100',
                'answer.*' => 'required|max:500',
            ]
        ],
    ],

    'blog' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
                'title.*' => 'required|max:200',
            ]
        ]
    ],

    'contact' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
                'title' => 'text',
                'sub_title' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:300',
                'title.*' => 'required|max:100',
                'sub_title.*' => 'required|max:350',
                'image.*' => 'sometimes|required|max:3072|image|mimes:jpg,jpeg,png'
            ],
        ],
        'multiple' => [
            'field_name' => [
                'name' => 'text',
                'value_one' => 'text',
                'value_two' => 'text',
            ],
            'validation' => [
                'name.*' => 'required|max:100',
                'value_one.*' => 'required|max:50',
                'value_two.*' => 'nullable'
            ],
        ],
    ],

    'countries' => [
        'single' => [
            'field_name' => [
                'heading' => 'text',
                'sub_heading' => 'text',
            ],
            'validation' => [
                'heading.*' => 'required|max:100',
                'sub_heading.*' => 'required|max:100',
            ]
        ],

    ],

    'footer'=>[
        'single' => [
            'field_name' => [
                'logo' => 'file',
                'location' => 'text',
                'email' => 'text',
                'phone' => 'text',
                'details' => 'textarea',
            ],
            'validation' => [
                'logo.*' => 'required|max:3072|image|mimes:jpg,jpeg,png',
                'location.*' => 'required|max:300',
                'email.*' => 'required|max:300',
                'phone.*' => 'required|max:300',
                'details.*' => 'required|max:400',
            ],
        ],
        'multiple' => [
            'field_name' => [
                'icon' => 'icon',
                'link' => 'url',
            ],
            'validation' => [
                'icon.*' => 'required|max:50',
                'link.*' => 'required|url',
            ],
        ],

    ],

    'login' => [
        'single' => [
            'field_name' => [
                'title_one' => 'text',
                'title_two' => 'text',
                'button_name' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'title_one.*' => 'required|max:100',
                'title_two.*' => 'required|max:100',
                'button_name.*' => 'required|max:200',
                'image.*' => 'sometimes|required|max:3072|image|mimes:jpg,jpeg,png'
            ]
        ],

    ],

    'register' => [
        'single' => [
            'field_name' => [
                'title_one' => 'text',
                'title_two' => 'text',
                'button_name' => 'text',
                'image' => 'file',
            ],
            'validation' => [
                'title_one.*' => 'required|max:100',
                'title_two.*' => 'required|max:100',
                'button_name.*' => 'required|max:200',
                'image.*' => 'sometimes|required|max:3072|image|mimes:jpg,jpeg,png'
            ]
        ],

    ],

    'user_verify' => [
        'single' => [
            'field_name' => [
                'title_one' => 'text',
                'image' => 'file',
                'image_two' => 'file'
            ],
            'validation' => [
                'title_one.*' => 'nullable|max:100',
                'image.*' => 'sometimes|required|max:3072|image|mimes:jpg,jpeg,png',
                'image_two.*' => 'sometimes|required|max:3072|image|mimes:jpg,jpeg,png'
            ]
        ],

    ],

    'message' => [
        'required' => 'This field is required.',
        'min' => 'This field must be at least :min characters.',
        'max' => 'This field may not be greater than :max characters.',
        'image' => 'This field must be image.',
        'mimes' => 'This image must be a file of type: jpg, jpeg, png.',
        'integer' => 'This field must be an integer value',
    ],

    'content_media' => [
        'image' => 'file',
        'image_two' => 'file',
        'logo' => 'file',
        'author_image' => 'file',
        'button_link_one' => 'url',
        'button_link_two' => 'url',
        'icon' => 'icon',
        'link' => 'url',
        'count_number' => 'number',
        'number' => 'number',
        'start_date' => 'date'
    ]
];

