<?php
require_once __DIR__ . "/User.php";

class UserModel {
    public static function getById($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $u = new User();
        $u->setNom($row['nom']);
        $u->setEmail($row['email']);
        $u->setPreferences($row['preferences']);
        $u->setAllergies($row['allergies']);
        $u->setPoids($row['poids']);
        $u->setAge($row['age']);
        $u->setCalories($row['calories']);
        $u->setId($row['id']);
        return $u;
    }

    public static function getByEmail($pdo, $email) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $u = new User();
        $u->setNom($row['nom']);
        $u->setEmail($row['email']);
        $u->setPreferences($row['preferences']);
        $u->setAllergies($row['allergies']);
        $u->setPoids($row['poids']);
        $u->setAge($row['age']);
        $u->setCalories($row['calories']);
        $u->setId($row['id']);
        return $u;
    }

    public static function save($pdo, User $user) {
        $email = $user->getEmail();
        $nom = $user->getNom();
        $prefs = $user->getPreferences();
        $allergies = $user->getAllergies();
        $poids = $user->getPoids();
        $age = $user->getAge();
        $calories = $user->getCalories();

        $existing = self::getByEmail($pdo, $email);

        if ($existing) {
            $sql = "UPDATE users SET nom = ?, preferences = ?, allergies = ?, poids = ?, age = ?, calories = ? WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $prefs, $allergies, $poids, $age, $calories, $email]);
            $user->setId($existing->getId());
        } else {
            $sql = "INSERT INTO users (nom, email, preferences, allergies, poids, age, calories) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $prefs, $allergies, $poids, $age, $calories]);
            $user->setId($pdo->lastInsertId());
        }

        // Synchronize junction tables
        // Note: saveProfile needs preferences and allergies to be arrays.
        // If they are strings from the form, we need to explode them.
        
        $user_for_sync = clone $user;
        if (is_string($user_for_sync->getPreferences())) {
            $user_for_sync->setPreferences(array_map('trim', explode(',', $user_for_sync->getPreferences())));
        }
        if (is_string($user_for_sync->getAllergies())) {
            $user_for_sync->setAllergies(array_map('trim', explode(',', $user_for_sync->getAllergies())));
        }

        self::saveProfile($pdo, $user_for_sync);

        return $user->getId();
    }

    public static function saveProfile($pdo, User $user) {
        $id_user = $user->getId();
        $preferences = $user->getPreferences(); 
        
        $pdo->prepare("DELETE FROM preferences_alimentaires WHERE id_user = ?")->execute([$id_user]);
        $pdo->prepare("DELETE FROM allergies WHERE id_user = ?")->execute([$id_user]);

        if (is_array($preferences)) {
            $stmtPref = $pdo->prepare("INSERT INTO preferences_alimentaires (id_user, type_preference, poids, age, calories) VALUES (?, ?, ?, ?, ?)");
            foreach ($preferences as $pref) {
                if ($pref === null || $pref === '') continue;
                $stmtPref->execute([$id_user, trim((string)$pref), $user->getPoids(), $user->getAge(), $user->getCalories()]);
            }
        }

        $allergies = $user->getAllergies();
        if (is_array($allergies)) {
            $stmtAll = $pdo->prepare("INSERT INTO allergies (id_user, nom_allergie) VALUES (?, ?)");
            foreach ($allergies as $all) {
                if ($all === null || $all === '') continue;
                $stmtAll->execute([$id_user, trim((string)$all)]);
            }
        }

        return true;
    }

    public static function getProfileDetails($pdo, $id_user) {
        $preferences = [];
        $allergies = [];

        $stmt1 = $pdo->prepare("SELECT type_preference FROM preferences_alimentaires WHERE id_user = ?");
        $stmt1->execute([$id_user]);
        while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            $preferences[] = $row['type_preference'];
        }

        $stmt2 = $pdo->prepare("SELECT nom_allergie FROM allergies WHERE id_user = ?");
        $stmt2->execute([$id_user]);
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $allergies[] = $row['nom_allergie'];
        }

        return [
            "preferences" => $preferences,
            "allergies" => $allergies
        ];
    }
}
