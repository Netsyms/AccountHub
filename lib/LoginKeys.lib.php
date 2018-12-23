<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

class LoginKey {

    public static function generate(string $appname, $appicon = null): string {
        global $database;
        do {
            $code = base64_encode(random_bytes(32));
        } while ($database->has('userloginkeys', ['key' => $code]));

        $database->insert('userloginkeys', ['key' => $code, 'expires' => date("Y-m-d H:i:s", time() + 600), 'appname' => $appname, 'appicon' => $appicon]);

        return $code;
    }

    public static function getuid(string $code): int {
        global $database;
        if (!$database->has('userloginkeys', ["AND" => ['key' => $code, 'uid[!]' => null]])) {
            throw new Exception();
        }

        $uid = $database->get('userloginkeys', 'uid', ['key' => $code]);

        return $uid;
    }

}
