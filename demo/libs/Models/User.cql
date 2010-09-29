
namespace Demo\Models;

User as users {
    
    id int primary key auto_increment;
    email text;
    password text;
    firstName text;
    lastName text;
    created timestamp;
    role integer;
    
    primaryKey = id;
    roles = [member => 1, admin => 2]
    fullName = CONCAT(firstName, ' ', lastName) as fullName;
    selectColumns = id, email, firstName, lastName, $fullName;
    SELECT = SELECT $selectColumns;
    
    static findAll() : self[] {
        $SELECT FROM $this
    }

    static findById($id) : self {
        $SELECT FROM $this WHERE id = $id
    }
    
    static count() : value {
        SELECT COUNT(*) FROM $this
    }
    
    insert() : last_insert_id {
        INSERT INTO $this (email, password, firstName, lastName, created)
        VALUES ($email, MD5($password), $firstName, $lastName, NOW())
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
