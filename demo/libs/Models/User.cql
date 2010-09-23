
namespace Models;

User as users {
    
    id integer;
    email text;
    password text;
    firstName text;
    lastName text;
    created timestamp;
    role integer;
    
    roles [member => 1, admin => 2]
    fullName { firstName || ' ' || lastName }
    selectColumns { id, email, $fullName }
    SELECT { SELECT $selectColumns }
    
    static findAll() : User[] {
        $SELECT FROM $this
    }

    static findById($id) : User {
        $SELECT FROM $this WHERE id = $id
    }
    
    static count() : value {
        SELECT COUNT(*) FROM $this
    }
    
    @UpdateWithLastIndex(id)
    insert() : null {
        INSERT INTO $this (email, password, firstName, lastName, created)
            VALUES ($email, MD5($password), $firstName, $lastName, NOW())
    }
    
    findMessages() -> Message::findAllByUser($id)
    addMessage($message) -> Message::create($id, $message)
}
