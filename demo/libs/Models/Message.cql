
namespace Demo\Models;

/**
 * Represents a message sent by the user
 */
Message represents messages returns \Demo\Message {

    id integer primary key autoincrement;
    user_id integer;
    message text;
    
    $selectColumns = messages.id, messages.user_id, messages.message;
    $SELECT = SELECT $selectColumns;
    
    static find($id) : self {
        $SELECT FROM $this WHERE id = $id
    }
    
    static findAllByUser($id) : self[] {
        $SELECT FROM $this WHERE user_id = $id
    }
    
    static findWithUser($id) : self + user:\Demo\User {
        $SELECT, @composite('user', $User::selectColumns)
        FROM $this JOIN users ON users.id = $this.user_id WHERE messages.id = $id
    }
    
    static create($user_id, $message) : null {
        INSERT INTO $this (user_id, message) VALUES ($user_id, $message)
    }
    
    static truncate() : null {
        DELETE FROM $this
    }
    
    insert() : last_insert_id {
        @create($user_id, $message)
    }
    
    update() : null {
        UPDATE $this SET message = $message WHERE id = $id
    }

}