
namespace Models;

/**
 * Represents a message sent by the user
 */
Message as messages {

    id int;
    user_id int;
    message text;

    static findAllByUser($id) : Message[] {
        SELECT * FROM $this WHERE user_id = $id
    }
    
    @UpdateWithLastIndex(id)
    insert() : null {
        INSERT INTO $this (user_id, message) VALUES ($user_id, $message)
    }

}