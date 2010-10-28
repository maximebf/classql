
namespace Demo\Models;

User represents users returns \Demo\User {
    
    id integer primary key autoincrement;
    email text;
    password text;
    firstName text;
    lastName text;
    created timestamp;
    
    $fullName = users.firstName || ' ' || users.lastName as fullName;
    $selectColumns = users.id, users.email, users.firstName, users.lastName, $fullName;
    $SELECT = SELECT $selectColumns;

    static find($id) : self {
        $SELECT FROM $this WHERE id = $id
    }
    
    static findAll() : self[] {
        $SELECT FROM $this
    }
    
    static findAllWithMessages() : self[] + messages:\Demo\Message[] {
        $SELECT, @composite('messages', $Message::selectColumns)
        FROM $this
        LEFT JOIN messages ON messages.user_id = users.id
    }
    
    static count() : value {
        SELECT COUNT(*) FROM $this
    }
    
    static truncate() : null {
        DELETE FROM $this
    }
    
    insert() : last_insert_id {
        INSERT INTO $this (email, password, firstName, lastName, created)
        VALUES ($email, $password, $firstName, $lastName, date('now'))
    }
    
    update() : null {
        UPDATE $this 
        SET email = $email, firstName = $firstName, lastName = $lastName 
        WHERE id = $id
    }
    
    fetchPassword() : update {
        SELECT password FROM $this WHERE id = $id
    }
    
    findMessages() -> Message::findAllByUser($id)
    addMessage($message) -> Message::create($id, $message)
}
