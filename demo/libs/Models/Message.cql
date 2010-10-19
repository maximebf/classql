
namespace Demo\Models;

/**
 * Represents a message sent by the user
 */
Message represents messages returns \Demo\Message {

    id integer primary key autoincrement;
    user_id integer;
    message text;
    
    static findAllByUser($id) : self[] {
        SELECT * FROM $this WHERE user_id = $id @build($filters)
    }
    
    static build($filters) {
        @if($filters[toto]) { AND toto = $filters[toto] }
    }
    
    static create($user_id, $message) : null {
        INSERT INTO $this (user_id, message) VALUES ($user_id, $message)
    }
    
    static truncate() : null {
        DELETE FROM $this
    }
    
    insert($toto) : last_insert_id {
        @create($user_id, $message)
    }
    
    update() : null {
        UPDATE $this SET message = $message WHERE id = $id
    }

}