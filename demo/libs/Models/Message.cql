
namespace Models;

/**
 * Represents a message sent by the user
 */
Message as messages {

    id int primary key auto_increment;
    user_id int;
    message text;
    
    primaryKey = id;

    static findAllByUser($id) : self[] {
        SELECT * FROM $this WHERE user_id = $id
    }
    
    static create($user_id, $message) : null {
        INSERT INTO $this (user_id, message) VALUES ($user_id, $message)
    }
    
    insert() : last_insert_id {
        INSERT INTO $this (user_id, message) VALUES ($user_id, $message)
    }
    
    update() : null {
        UPDATE $this SET message = $message WHERE id = $id
    }

}