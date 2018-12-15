<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

$APIS = [
    "ping" => [
        "load" => "ping.php",
        "vars" => [
        ],
        "permission" => [
        ]
    ],
    "auth" => [
        "load" => "auth.php",
        "vars" => [
            "username" => "string",
            "password" => "string"
        ]
    ],
    "userinfo" => [
        "load" => "userinfo.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ]
        ]
    ],
    "userexists" => [
        "load" => "userexists.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ]
        ]
    ],
    "hastotp" => [
        "load" => "hastotp.php",
        "vars" => [
            "username" => "string"
        ]
    ],
    "verifytotp" => [
        "load" => "verifytotp.php",
        "vars" => [
            "username" => "string",
            "code" => "string"
        ]
    ],
    "acctstatus" => [
        "load" => "acctstatus.php",
        "vars" => [
            "username" => "string"
        ]
    ],
    "login" => [
        "load" => "login.php",
        "vars" => [
            "username" => "string",
            "password" => "string"
        ]
    ],
    "ismanagerof" => [
        "load" => "ismanagerof.php",
        "vars" => [
            "manager" => "string",
            "employee" => "string",
            "uid (optional)" => "numeric"
        ]
    ],
    "getmanaged" => [
        "load" => "getmanaged.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ],
            "get (optional)" => "string"
        ]
    ],
    "getmanagers" => [
        "load" => "getmanagers.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ]
        ]
    ],
    "usersearch" => [
        "load" => "usersearch.php",
        "vars" => [
            "search" => "string"
        ]
    ],
    "permission" => [
        "load" => "permission.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ],
            "code" => "string"
        ]
    ],
    "mobileenabled" => [
        "load" => "mobileenabled.php"
    ],
    "mobilevalid" => [
        "load" => "mobilevalid.php",
        "vars" => [
            "username" => "string",
            "code" => "string"
        ]
    ],
    "alertemail" => [
        "load" => "alertemail.php",
        "vars" => [
            "username" => "string",
            "appname (optional)" => "string"
        ]
    ],
    "codelogin" => [
        "load" => "codelogin.php",
        "vars" => [
            "code" => "string"
        ]
    ],
    "listapps" => [
        "load" => "listapps.php"
    ],
    "getusersbygroup" => [
        "load" => "getusersbygroup.php",
        "vars" => [
            "gid" => "numeric",
            "get (optional)" => "string"
        ]
    ],
    "getgroupsbyuser" => [
        "load" => "getgroupsbyuser.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ]
        ]
    ],
    "getgroups" => [
        "load" => "getgroups.php"
    ],
    "groupsearch" => [
        "load" => "groupsearch.php",
        "vars" => [
            "search" => "string"
        ]
    ],
    "checkpin" => [
        "load" => "checkpin.php",
        "vars" => [
            "pin" => "string",
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ]
        ]
    ],
    "getnotifications" => [
        "load" => "getnotifications.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ]
        ]
    ],
    "readnotification" => [
        "load" => "readnotification.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ],
            "id" => "numeric"
        ]
    ],
    "addnotification" => [
        "load" => "addnotification.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ],
            "title" => "string",
            "content" => "string",
            "timestamp (optional)" => "string",
            "url (optional)" => "string",
            "sensitive (optional)" => "string"
        ]
    ],
    "deletenotification" => [
        "load" => "deletenotification.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ],
            "id" => "numeric"
        ]
    ],
];
