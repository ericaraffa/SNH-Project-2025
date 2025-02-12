USE snh;

CREATE TABLE `user`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(200) NOT NULL,
    `username` VARCHAR(200) NOT NULL,
    `password` CHAR(64) NOT NULL,
    `verified` BOOLEAN DEFAULT FALSE,
    `locked` BOOLEAN DEFAULT FALSE,
    `premium` BOOLEAN DEFAULT FALSE,

    PRIMARY KEY (`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `user_verification`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `user_recover`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,
    `valid_until` DATETIME NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `session`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,
    `valid_until` DATETIME NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

# TODO Delete this
CREATE TABLE `book`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `author` VARCHAR(200) NOT NULL,
    `genre` VARCHAR(200) NOT NULL,
    `picture` VARCHAR(200) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,

    `price` INTEGER NOT NULL,

    PRIMARY KEY (`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `novel`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `text` TEXT DEFAULT NULL,
    `premium` BOOLEAN DEFAULT FALSE,

    PRIMARY KEY (`id`)
) CHARACTER SET=utf8mb4;

# TODO Delete this
CREATE TABLE `order`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `total` INTEGER NOT NULL,
    `shipping_address` VARCHAR(500),
    `shipping_city` VARCHAR(200),
    `shipping_state` VARCHAR(200),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

# TODO Delete this
CREATE TABLE `order_book`(
    `order_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `quantity` INT NOT NULL,

    PRIMARY KEY (`order_id`, `book_id`),
    FOREIGN KEY (`order_id`) REFERENCES `order`(`id`),
    FOREIGN KEY (`book_id`) REFERENCES `book`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `wrong_login`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

CREATE TABLE `user_lock`(
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` CHAR(64) NOT NULL,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
) CHARACTER SET=utf8mb4;

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;


INSERT INTO user VALUES
    (DEFAULT, 'email', 'username', 'pwd', TRUE, FALSE, TRUE);


INSERT INTO novel VALUES 
    (DEFAULT, "Claudione", "OOOOOOOOOOOOOOOOOOOOOOOOOOOOOO
NONOSTANTE NELLA MIA CREDENZA SI NOTI L'ASSANZA DI ZUCCHERO E THEEEEEE
SONO ANCORA IL SIGNORE DEL MONDO PIu' RICCO CHE C'e'
IO
NONOSTANTE NEL MIO GUARDAROBA DI TUTTA LA ROBA E' RIMASTO UN GILEEEET
SONO ANCORA IL SIGORE DEL MONDO PIU' RICCO CHE C'E'
NONOSTANTE LA SUA TRACOTANZA E MI MANCA LA GRASSA SUI CLAUDIO GLASSEEEEEE
SONO LO SGRUDOLO DEL MONDO PIU' RICCO CHE C'E'
NONOSTANTE I DIVANI E I BIDEEEET
SONO ANCORA IL PIU' RICCO CHE C'E'
NONOSTANTE I MIEI CAZZI SIAN TREEEE
SONO ANCORA IL PIU' RICCO CHE C'E'", FALSE),
    (DEFAULT, "Plenty of Lasagna, Today se magna", DEFAULT, TRUE);


# TODO Change this
INSERT INTO book VALUES 
    (DEFAULT, 'Spaghetti Hacker', 'Stefano Chiccarelli', 'History, Computer science, Italy', 'spaghetti-hacker.jpg', "Grazie a questo libro, è possibile capire come e perché sono nati gli Spaghetti Hacker, e in che cosa, oggi, si sono trasformati. Ma soprattutto, quali sono le cause che hanno trasformato la rete italiana in un colosso dai piedi d'argilla.", 1960),
    (DEFAULT, 'Doctor Newtron', 'Dario Bressanini', 'History, Comics, Science', 'doctor-newtron.jpg', "Scienziato e supereroe, capace di controllare gli elementi trasformando a piacimento la materia, Doctor Newtron è uno dei più amati e leggendari personaggi del fumetto. Allora, perché il suo nome suona nuovo?
Forse perché non è mai esistito. Sia lui che tutta
la sua lunga e avvincente parabola editoriale sono frutto della fantasia di Dario Bressanini, che si finge soltanto curatore e compilatore di questa antologia, rendendola totalmente credibile e coerente con la storia del fumetto. Mentre invece è autore di tutte le avventure di Doc Newtron, di cui ha scritto soggetti e sceneggiature, che riflettono lo spirito dell'epoca, affidate a un team di disegnatori capaci di omaggiare i grandi maestri dei comics americani mantenendo sempre una cifra stilistica fresca e attuale.
Supportato da un ampio apparato testuale, questo fumetto-saggio risulta un'opera acuta e ironica, appassionante e divertente, in cui Dario Bressanini unisce il fumetto alla scienza, raccontando con una riflessione profonda e originale (e facendo letteralmente vedere) come scienziati e avvenimenti del mondo reale, da Oppenheimer al lancio dello Sputnik al Progetto genoma umano, si siano riverberati nei fumetti dei supereroi cambiando la rappresentazione della scienza e degli scienziati nell'immaginario collettivo, influenzandone a sua volta la società.", 1299),
    (DEFAULT, 'I Hate PHP: A Beginner''s Guide to PHP and MySQL PLR Version', ' M P L X P ', 'Philosophy, Computer Science, Coding', 'php.jpg', "So, who is this book for? This book is for the lonely people who can't get a da...hold on...wrong book...”I Hate PHP” is for webmasters and designers who have always wondered what PHP could do for them; someone who wants to “do” some PHP and not outsource it. It is best to be familiar with HTML and writing / deciphering HTML code. You don't need to be a top-notch “I do it all in Notepad” kinda person - just being able to open up a text editor and understand the code is enough. Copy / Paste knowledge is helpful too.", 1288),
    (DEFAULT, 'The Web Application Hacker''s Handbook', 'Dafydd Stuttard', 'Computer science, Security', 'web-hacking.jpg', "The highly successful security book returns with a new edition, completely updated
Web applications are the front door to most organizations, exposing them to attacks that may disclose personal information, execute fraudulent transactions, or compromise ordinary users. This practical book has been completely updated and revised to discuss the latest step-by-step techniques for attacking and defending the range of ever-evolving web applications. You'll explore the various new technologies employed in web applications that have appeared since the first edition and review the new attack techniques that have been developed, particularly in relation to the client side.

Reveals how to overcome the new technologies and techniques aimed at defending web applications against attacks that have appeared since the previous edition
Discusses new remoting frameworks, HTML5, cross-domain integration techniques, UI redress, framebusting, HTTP parameter pollution, hybrid file attacks, and more
Features a companion web site hosted by the authors that allows readers to try out the attacks described, gives answers to the questions that are posed at the end of each chapter, and provides a summarized methodology and checklist of tasks
Focusing on the areas of web application security where things have changed in recent years, this book is the most current resource on the critical topic of discovering, exploiting, and preventing web application security flaws.", 4508),
    (DEFAULT, 'Guerre di rete', 'Carola Frediani', 'History, Politics, Computer Science', 'guerre_di_rete.jpg', "Dai retroscena sulla prima 'arma digitale' usata da hacker al soldo dei governi per sabotare un impianto industriale ai ricercatori di cyber-sicurezza finiti al centro di intrighi internazionali degni di James Bond; dai virus informatici usati per le estorsioni di massa fino al mercato sotterraneo dei dati personali degli utenti. “Guerre di Rete” racconta come Internet stia diventando sempre di più un luogo nel quale governi, agenzie, broker di attacchi informatici e cyber-criminali ora si contrappongono, ora si rimescolano in uno sfuggente gioco delle parti. A farne le spese sono soprattutto gli utenti normali - anche quelli che dicono « non ho nulla da nascondere» =, carne da cannone di un crescente scenario di (in)sicurezza informatica dove ai primi virus artigianali si sono sostituite articolate filiere cyber-criminali in continua ricerca di modelli di business e vittime da spolpare. In questo contesto emergono costantemente nuove domande. La crittografia è davvero un problema per l'antiterrorismo? Quali sono le frontiere della sorveglianza statale? Esiste davvero una contrapposizione tra privacy e sicurezza? Carola Frediani scava in alcune delle storie più significative di questo mondo nascosto, intervistando ricercatori, attivisti, hacker, cyber-criminali, incontrandoli nei loro raduni fisici e nelle loro chat.", 950);
