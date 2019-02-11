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
        ],
        "keytype" => "NONE"
    ],
    "auth" => [
        "load" => "auth.php",
        "vars" => [
            "username" => "string",
            "password" => "string",
            "apppass (optional)" => "/[0-1]/"
        ],
        "keytype" => "AUTH"
    ],
    "userinfo" => [
        "load" => "userinfo.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ]
        ],
        "keytype" => "READ"
    ],
    "userexists" => [
        "load" => "userexists.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ]
        ],
        "keytype" => "AUTH"
    ],
    "hastotp" => [
        "load" => "hastotp.php",
        "vars" => [
            "username" => "string"
        ],
        "keytype" => "AUTH"
    ],
    "verifytotp" => [
        "load" => "verifytotp.php",
        "vars" => [
            "username" => "string",
            "code" => "string"
        ],
        "keytype" => "AUTH"
    ],
    "acctstatus" => [
        "load" => "acctstatus.php",
        "vars" => [
            "username" => "string"
        ],
        "keytype" => "AUTH"
    ],
    "login" => [
        "load" => "login.php",
        "vars" => [
            "username" => "string",
            "password" => "string",
            "apppass (optional)" => "/[0-1]/"
        ],
        "keytype" => "AUTH"
    ],
    "ismanagerof" => [
        "load" => "ismanagerof.php",
        "vars" => [
            "manager" => "string",
            "employee" => "string",
            "uid (optional)" => "numeric"
        ],
        "keytype" => "READ"
    ],
    "getmanaged" => [
        "load" => "getmanaged.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ],
            "get (optional)" => "string"
        ],
        "keytype" => "READ"
    ],
    "getmanagers" => [
        "load" => "getmanagers.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ]
        ],
        "keytype" => "READ"
    ],
    "usersearch" => [
        "load" => "usersearch.php",
        "vars" => [
            "search" => "string"
        ],
        "keytype" => "READ"
    ],
    "permission" => [
        "load" => "permission.php",
        "vars" => [
            "OR" => [
                "username" => "string",
                "uid" => "numeric"
            ],
            "code" => "string"
        ],
        "keytype" => "READ"
    ],
    "mobileenabled" => [
        "load" => "mobileenabled.php",
        "keytype" => "NONE"
    ],
    "mobilevalid" => [
        "load" => "mobilevalid.php",
        "vars" => [
            "username" => "string",
            "code" => "string"
        ],
        "keytype" => "AUTH"
    ],
    "alertemail" => [
        "load" => "alertemail.php",
        "vars" => [
            "username" => "string",
            "appname (optional)" => "string"
        ],
        "keytype" => "FULL"
    ],
    "codelogin" => [
        "load" => "codelogin.php",
        "vars" => [
            "code" => "string"
        ],
        "keytype" => "AUTH"
    ],
    "listapps" => [
        "load" => "listapps.php",
        "keytype" => "NONE"
    ],
    "getusersbygroup" => [
        "load" => "getusersbygroup.php",
        "vars" => [
            "gid" => "numeric",
            "get (optional)" => "string"
        ],
        "keytype" => "READ"
    ],
    "getgroupsbyuser" => [
        "load" => "getgroupsbyuser.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ]
        ],
        "keytype" => "READ"
    ],
    "getgroups" => [
        "load" => "getgroups.php",
        "keytype" => "READ"
    ],
    "groupsearch" => [
        "load" => "groupsearch.php",
        "vars" => [
            "search" => "string"
        ],
        "keytype" => "READ"
    ],
    "checkpin" => [
        "load" => "checkpin.php",
        "vars" => [
            "pin" => "string",
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ]
        ],
        "keytype" => "AUTH"
    ],
    "getnotifications" => [
        "load" => "getnotifications.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ]
        ],
        "keytype" => "READ"
    ],
    "readnotification" => [
        "load" => "readnotification.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ],
            "id" => "numeric"
        ],
        "keytype" => "FULL"
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
        ],
        "keytype" => "FULL"
    ],
    "deletenotification" => [
        "load" => "deletenotification.php",
        "vars" => [
            "OR" => [
                "uid" => "numeric",
                "username" => "string"
            ],
            "id" => "numeric"
        ],
        "keytype" => "FULL"
    ],
    "getloginkey" => [
        "load" => "getloginkey.php",
        "vars" => [
            "appname" => "string",
            "appicon (optional)" => "string"
        ],
        "keytype" => "AUTH"
    ],
    "checkloginkey" => [
        "load" => "checkloginkey.php",
        "vars" => [
            "code" => "string"
        ],
        "keytype" => "AUTH"
    ]
];
