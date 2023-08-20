<?php
namespace App\Model;

class Music
{
    const LYRICS = [
        // U Can't Touch This
        'Go with the flow; it is said: that if you can\'t groove to this then you are probably dead!',

        // Katamari Damacy
        'Na naaaaaaa... na-na-na-na-na, na naaaaa... na-na na na-na naaaaaaa...',

        // Manha-manha
        'Manha-manha - do dooooo d-do-do.... manha-manha - do do-do do...',

        // Tom's Diner
        'There\'s a woman, on the outside, looking inside; does she see me? No she does not really see me, \'cause she sees her own reflection...',

        // It's the End of the World As We Know It
        'Speed it up a notch, speed, grunt, no strength - the ladder starts to clatter with a fear of height-- down-- height!',

        // Things That Don't Exist
        'Perfect circles, three-sided squares, and two nested pairs with just one number; Issac Newton\'s fourth law of motion; rivers and oceans on the mooooon...',

        // Nano Nano
        'Nano naaanooo, naaaanoooo - what a wonderful surprise! The ordinary is extraordinary when you make it nano size!',

        // Homestar Runner
        'I don\'t know... who it is... but it probably is Fhqwhgaaaaads. I asked my friend Joe; I asked my friend Jake: they said it was Fhqwhgads!',

        // Portal
        'This was a triumph. I\'m making a note here: huge success. It\'s haarrd to ooooverstaaaate my saaaatisfadtion...',

        // B-52s
        'Everybody\'s movin\'; everybody\'s groovin\', baby! Folks linin\' up outside just to get down!',

        // Weird Al
        'Eeaaat it! Eeaaat it! Open up your mouth and feed it! Have a banana-- have a whole bunch! It doesn\'t matter what you had for lunch! Just eat it! (Eat it, eat it, eat it...)',
        'I think I\'m a clone now... there\'s always two of me just a-hangin\' arou-ou-ound...',

        // Sonic (the Hedgehog)
        'Rollin\' around at the speed of sou-ound... got places to go - gotta\' follow my rain-bow!',

        // Beck, Timebomb
        'We\'re going sideways! Highways! Riding on an elevator - cold just like an alligator - now my baby\'s out of date!',

        // Benassi Bros, Light
        'Love is all and love is round. Like a precious flower. I\'m a spirit floating down. In the universe.',

        // Katzenjammer, Demon Kitty Rag
        'I\'ll be your nightmare mirror! Dubba-do-what you did do-do to meeeee - ahahahaha! I\'ll be your nightmare mirorrrrr-oh-oh-or... colder than a steel blade, yeah...',

        // Pogo, Data & Picard
        'Incredibly unbroken sentence; moving from topic to topic; no one had a chance to interrupt. It was quite hypnotic.',

        // Pokemon, anime american opening song
        'I wanna be the very best, like no one ever was... to catch them is my real test; to train them is my cause!',

        // Dragostea Din Tei
        'Vrei să pleci dar nu mă, nu mă iei! Nu mă, nu mă iei! Nu mă, nu mă, nu mă iei!',

        // The Hamsterdance
        'All right, everybody, now here we go! It\'s a brand new version of the do-see-do!',

        // Skrillex, Seventeen
        'I want to write you a nooooote... that you\'ll never read. My friends keep telling meeeee... I shouldn\'t beg and plead...',

        // Zedd
        'Let\'s get looooo-o-o-o-o-ooost... at sea-ea-ea-ea, ea-ea-ea, ea! Where theeyyy will neeever find us! Got staarrs at niiight to guide us!',

        // System of a Down
        'Sooooomewhere! Between the saaacred silence and sleep! Disorder! Disorder! Disooooor-o-o-orrrrr-derrrrrrrrr!',

        // Infected Mushroom
        'At night. I sit by your side. Waiting for you... to give me a sign. I\'m counting the daaaay-ays... and have nothing to say-ay...',
        'When I\'m hi-i-iding... amid theeee throng... but nowhere is safe... from the ancient sooo-ooo-ooooong!',
        'I want to move, to lose, to take the grooves... and to give it all back...',

        // Mario Kart Lovesong
        'No one will touch us if we pick up a star. And if you spin out, you can ride in my car. When we slide together we generate sparks in our wheels, and our hearts...',

        // Caravan Palace
        'Act like a brother (every day is a miracle). Help one another (connect back with the people). Give it to your lover (and all the people you miss). Let\'s go, already...',

        // Klingon Victory Song
        'yIja\'Qo\', Bagh Da tuH mogh, ChojaH Duh rHo... yIjah, Qey\' \'oH! yIjah, Qey\' \'oH! yIjah, Qey\' \'oH!',

        // Fresh Prince of Bel-Air
        'Chillin\' out, maxin\', relaxin\', all cool... and all shootin\' some b-ball outside of the school...',

        // Mitternaaaaacht!
        'Loca in ferna in nocte... loca in ferna in nocte... animae in nebula... Mitternaaaaacht!',

        // Aqua, Doctor Jones
        'Doctor Jones, Jones, calling Doctor Jones... Doctor Jones, Doctor Jones, get up now (wake up now)...',

        // They Might Be Giants
        'I found my miiiiind... on the ground belooowww... I was looking dooowwwn... it was looking baaaaack... I was in the sky, all dressed in black!',
        'Placental, the sister of her brother ma-arsupiaaal... their cousin called Monotreme; dead uncle alotheeerrriaaan...',

        // Alan Parsons Project, Eye in the Sky
        'I am the eye in the sky... looking at yoo-oou... I can read your mind. I am the maker of rules, dealing with foo-ools... I can cheat you blind.',

        // Alanis Morissette
        'And I\'m he-ere! To remind you! Of the mess you left when you went away!',

        // The Cranberries, Dreams
        'Ohhh, myyy, liiife... is changing every daaay... in every possible way-ay...',

        // Walk Like an Egyptian
        'Slide your feet up street, bend your back, shift your arm, then you pull it back. Life is hard, you know - oh-way-oh! - so strike a pose on a Cadillac...',

        // The Scatman
        'While you\'re still sleeping, the saints are still weeping, \'cause things you called dead haven\'t yet had the chance to be be born...',

        // Gangum Style
        'Heeeeeyyyyy, sexy la-dy! 오, 오 오 오! 오빤 강남스타일!',

        // Courtney Barnett
        'My hands are shaaaky; my knees are weeaak; I can\'t seem to stand on my own two feeet...',

        // Chumbawumba
        'I get knocked down! But I get up again! You\'re never gonna keep me down!',
        'She\'s a clueless social climber; likes the wrong side of the bed. She\'s a pick-me-up, and she\'s a drink-to-me in the company of friends...',

        // Monty Python
        'For life is quite absurd, and death\'s the final word; you must always face the curtain with a booww...',
        'Just remember that you\'re standing... on a planet... that\'s evolving... and revolving at nine-hundred miles an hour...',

        // Tim Minchin
        'I know in the past my outlook has been limited... I couldn\'t see examples of where life had been definitive...',

        // Gorillaz
        'You got a new horizon, it\'s ephemeral style... a melancholy town where we never smile...',

        // Fatboy Slim, Weapon of Choice
        'Halfway between the gutter, and, the stars. Yeah. Halfway between the gutter, and, the stars...',

        // Flobos, Handlebars
        'Me and my friend saw a platypus; me and my friend made a comic book. And guess how long it took? I can do anything that I want, \'cause, look...',

        // Rolling Stones
        'The floods is threat\'ning my very life today. Gimme-- gimme shelter, or I\'m gonna fade away...',

        // Bad Reputation
        'I don\'t give a damn \'bout my reputation! ... I\'ve never been afraid of any deviation! ... And I don\'t really care if you think I\'m strange; I ain\'t gonna change!',

        // I Monster, Daydream in Blue
        'Daydream-- I dream of you amid the flowers. For a couple of hours. Such a beautiful daaaayyyy...',

        // Eiffel 64, Blue
        'I\'m blue, da-ba-dee, da-ba-die! A da-ba-dee... da-ba-die, a da-ba-dee, da-ba-die...',

        // Mary Poppins
        'Now where is there a more happier crew... than thems that sing chim chim cher-ee, chim cher-oo...',

        // Sayonara Wild Hearts
        'And all the things I need to say... and all the big words seem to stay... on the insiiide... on the insi-i-iiide!',
        'I don\'t know... where to start... in the search of the beat of your heart... a sooouuund in the deeeaaafening silence...',
    ];
}
