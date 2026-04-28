<?php
class UserModel {

    // 🔹 Récupérer les préférences et allergies
    public static function getProfile($pdo, $id_user) {

        $preferences = [];
        $allergies = [];

        // Préférences
        $query1 = "SELECT type_preference FROM Preferences_Alimentaires WHERE id_user = ?";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->execute([$id_user]);

        while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            $preferences[] = $row['type_preference'];
        }

        // Allergies
        $query2 = "SELECT type_allergie FROM Allergies WHERE id_user = ?";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->execute([$id_user]);

        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $allergies[] = $row['type_allergie'];
        }

        return [
            "preferences" => $preferences,
            "allergies" => $allergies
        ];
    }

    // 🔹 Enregistrer les données
    public static function saveProfile($pdo, $id_user, $preferences, $allergies) {

        // Supprimer anciennes données
        $stmtDelPref = $pdo->prepare("DELETE FROM Preferences_Alimentaires WHERE id_user = ?");
        $stmtDelPref->execute([$id_user]);

        $stmtDelAll = $pdo->prepare("DELETE FROM Allergies WHERE id_user = ?");
        $stmtDelAll->execute([$id_user]);

        // Ajouter préférences
        $stmtPref = $pdo->prepare("INSERT INTO Preferences_Alimentaires (id_user, type_preference) VALUES (?, ?)");
        foreach ($preferences as $pref) {
            $stmtPref->execute([$id_user, trim((string)$pref)]);
        }

        // Ajouter allergies
        $stmtAll = $pdo->prepare("INSERT INTO Allergies (id_user, type_allergie) VALUES (?, ?)");
        foreach ($allergies as $all) {
            $stmtAll->execute([$id_user, trim((string)$all)]);
        }

        return true;
    }
}
?>