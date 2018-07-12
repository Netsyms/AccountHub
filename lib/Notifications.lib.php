<?php

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

class Notifications {

    /**
     * Add a new notification.
     * @global $database
     * @param User $user
     * @param string $title
     * @param string $content
     * @param string $timestamp If left empty, the current date and time will be used.
     * @param string $url
     * @param bool $sensitive If true, the notification is marked as containing sensitive content, and the $content might be hidden on lockscreens and other non-secure places.
     * @return int The newly-created notification ID.
     * @throws Exception
     */
    public static function add(User $user, string $title, string $content, string $timestamp = "", string $url = "", bool $sensitive = false): int {
        global $database, $Strings;
        if ($user->exists()) {
            if (empty($title) || empty($content)) {
                throw new Exception($Strings->get("invalid parameters", false));
            }

            $timestamp = date("Y-m-d H:i:s");
            if (!empty($timestamp)) {
                $timestamp = date("Y-m-d H:i:s", strtotime($timestamp));
            }

            $database->insert('notifications', ['uid' => $user->getUID(), 'timestamp' => $timestamp, 'title' => $title, 'content' => $content, 'url' => $url, 'seen' => 0, 'sensitive' => $sensitive]);
            return $database->id() * 1;
        }
        throw new Exception($Strings->get("user does not exist", false));
    }

    /**
     * Fetch all notifications for a user.
     * @global $database
     * @param User $user
     * @return array
     * @throws Exception
     */
    public static function get(User $user) {
        global $database, $Strings;
        if ($user->exists()) {
            $notifications = $database->select('notifications', ['notificationid (id)', 'timestamp', 'title', 'content', 'url', 'seen', 'sensitive'], ['uid' => $user->getUID()]);
            for ($i = 0; $i < count($notifications); $i++) {
                $notifications[$i]['id'] = $notifications[$i]['id'] * 1;
                $notifications[$i]['seen'] = ($notifications[$i]['seen'] == "1" ? true : false);
                $notifications[$i]['sensitive'] = ($notifications[$i]['sensitive'] == "1" ? true : false);
            }
            return $notifications;
        }
        throw new Exception($Strings->get("user does not exist", false));
    }

    /**
     * Mark the notification identified by $id as read.
     * @global $database
     * @global $Strings
     * @param User $user
     * @param int $id
     * @throws Exception
     */
    public static function read(User $user, int $id) {
        global $database, $Strings;
        if ($user->exists()) {
            if ($database->has('notifications', ['AND' => ['uid' => $user->getUID(), 'notificationid' => $id]])) {
                $database->update('notifications', ['seen' => 1], ['AND' => ['uid' => $user->getUID(), 'notificationid' => $id]]);
                return true;
            }
            throw new Exception($Strings->get("invalid parameters", false));
        }
        throw new Exception($Strings->get("user does not exist", false));
    }

    public static function delete(User $user, int $id) {
        global $database, $Strings;
        if ($user->exists()) {
            if ($database->has('notifications', ['AND' => ['uid' => $user->getUID(), 'notificationid' => $id]])) {
                $database->delete('notifications', ['AND' => ['uid' => $user->getUID(), 'notificationid' => $id]]);
                return true;
            }
            throw new Exception($Strings->get("invalid parameters", false));
        }
        throw new Exception($Strings->get("user does not exist", false));
    }
}
