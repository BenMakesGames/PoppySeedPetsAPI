<?php
namespace App\Model;

class Music
{
    const LYRICS = [
        // U Can't Touch This
        'Go with the flow; it is said: that if you can\'t groove to this then you are probably dead!',

        // Katamari Damacy
        'Na naaaaaaa... na-na-na-na-na, na naaaaa... na-na na na-na naaaaaaa...',
        'Every day, every night, let\'s do the royal rainbow - yes! The cosmic message of looove!',
        'I love you, iki ga tomaru kurai sou... I miss you, tsuyoku dakishimete itsumademo...',

        // Manha-manha
        'Manha-manha - do dooooo d-do-do.... manha-manha - do do-do do...',

        // Tom's Diner
        'There\'s a woman, on the outside, looking inside; does she see me? No she does not really see me, \'cause she sees her own reflection...',

        // It's the End of the World As We Know It
        'Speed it up a notch, speed, grunt, no strength - the ladder starts to clatter with a fear of height-- down-- height!',

        // Things That Don't Exist
        'Perfect circles, three-sided squares, and two nested pairs with just one number; Issac Newton\'s fourth law of motion; rivers and oceans on the mooooon...',

        // Nano Nano
        'Nano naaanooo, naaaanoooo - what a wonderful surpriiise! The ordinary is extraordinary when you make it nano siiize!',

        // Homestar Runner
        'I don\'t know... who it is... but it probably is Fhqwhgaaaaads. I asked my friend Joe; I asked my friend Jake: they said it was Fhqwhgads!',

        // Portal
        'This was a triumph. I\'m making a note here: huge success. It\'s haarrd to ooooverstaaaate my saaaatisfaction...',

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

        // Pogo, Data & Picard, and Boy & Bear
        'Incredibly unbroken sentence; moving from topic to topic; no one had a chance to interrupt. It was quite hypnotic.',
        'Dum-dee-dum deeee dum-dum. Dum-dee-dum deeee dum-dum. When I\'m with you, I\'m with you.',

        // Pokémon, anime american opening song
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
        'They revaaamped the airpoorrt completely, now it looks just like a nightcluuub... everyone\'s exciteeed and confuu-uused...',
        'I heard they have a space program - when they sing you can\'t hear, there\'s no air. Sometiiimes I think I kind of like that and other times I think I\'m already there...',

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

        // Feel Good Inc., by Gorillaz
        'You got a new horizon, it\'s ephemeral style... a melancholy town where we never smile...',

        // Fatboy Slim, Weapon of Choice
        'Halfway between the gutter, and, the stars. Yeah. Halfway between the gutter, and, the stars...',

        // Flobots, Handlebars
        'Me and my friend saw a platypus; me and my friend made a comic book. And guess how long it took? I can do anything that I want, \'cause, look...',

        // Rolling Stones
        'The floods is threat\'ning my very life today. Gimme-- gimme shelter, or I\'m gonna fade away...',

        // Bad Reputation
        'I don\'t give a damn \'bout my reputation! ... I\'ve never been afraid of any deviation! ... And I don\'t really care if you think I\'m strange; I ain\'t gonna change!',

        // I Monster, Daydream in Blue
        'Daydream. I dream of you amid the flowers. For a couple of hours. Such a beautiful daaaayyyy...',

        // Eiffel 64, Blue
        'I\'m blue, da-ba-dee, da-ba-die! A da-ba-dee... da-ba-die, a da-ba-dee, da-ba-die...',

        // Mary Poppins
        'Now where is there a more happier crew... than thems that sing chim chim cher-ee, chim cher-oo...',

        // Sayonara Wild Hearts
        'And all the things I need to say... and all the big words seem to stay... on the insiiide... on the insi-i-iiide!',
        'I don\'t know... where to start... in the search of the beat of your heart... a sooouuund in the deeeaaafening silence...',

        // Studio Killers, Friday Night Gurus
        'Theeyy\'ve got a soouund... serious-ly obese in the base frequencies; peerrfectly round, like spiiirals iiin their DNAaa...',

        // Portugal, Feel It Still
        'Ooh-woo, I\'m a rebel just for kicks, now... I been feeling it since 1966, now...',

        // Dirty Vegas, Days Go By
        'You. You are still a whisper on my lips... a feeling at my fingertips... that\'s pulling at my skin...',

        // Todrick Hall, Nails, Hair, Hips, Heels
        'Girl, I don\'t dance, I work; I don\'t play, I slay; I don\'t walk, I strut, strut, strut, and then sashay...',

        // from Jet Set Radio
        'I\'m trying to get to-- I\'m trying to get to sleep! Playing with that-- playing with that-- I\'m, I\'m, I\'m... aaaahhh!',
        'The most important part of dance is music. So now let us listen to the music, and identify the beats. One... ... two... ... three... ... but that was too soft.',

        // Faster than Light (from Stellaris)
        'Stars in the skyyy... Floating in darrrkneeess.... Soooon... I will flyyy... faaasterrr thaaan liiiiight...',

        // As The Rush Comes
        'Traveling somewherrre; could be aaanywhere. There\'s a coldness in the air... but I don\'t ca-arrre...',

        // Pendulum
        'It\'s 9,000 miii-iles back to yooo-ooou. (Nooo-ooo...) I still feeeel like hooooome is in... yoouur aarrms...',

        // Venus Hum, Hummingbirds
        'Some of my faaa-aaavourite colours in the world... beat against my eyelids with the blues of green hummingbirds...',

        // Freezepop
        'The music is loud. The night is so young. All over the world. We wanna have fun.',
        'You tell just half the truth, you\'re pulling strings and pushing buttons. Wheels are turning in your head; I know that you are up to something...',

        // Phoenix
        'No, I gotta be someone else. These days it comes, it comes, it comes, it comes, it comes and goes...',

        // I:Scintilla, The Bells
        'The florrrescent lightiiing does nothiiing to keep you from hiiiiiidiiiiiiiiiiiiiii i-i-iyeaaaaaaaaahah-ah, ah-aaahhh...!',

        // Group Love, Tongue Tied
        'Don\'t take me tongue tied... don\'t wave no goodbye... dooooooooooon\'t BREAK! (One, two, three, four...)',

        // Boom Boom Satellites, Shut Up and Explode
        'Running free, running free, driving me insane... shut it down, shut it down, it\'s about to explode... run away, run away, run away, run away, run away, run away, run away, run away, run away, run away, run away, run away, run away, run away, run away, run away...',

        // Fall Out Boy, I Don't Care
        'I! Don\'t! Care what you think, as long as it\'s abouuut me; the best of us can find happiness in mi-i-i-isery...',

        // Lush, De-Luxe
        'Some say I\'m vaaauuuge... and I\'d easily faaade... foolish paraaade ooof faaantaaasyyyy...',

        // The Seatbelts, Yo Pumpkin Head
        'In the dream that pulls you along, won\'t go carry a jelly bean. In your dream they\'re never on top. Let\'s get funky, Pumpkinheeeeeaaaaad! Yo, Pumpkinheeeeeaaaaad! Yo, Pumpkinheeeeeaaaaad! Yo, Pumpkinheeeeeaaaaad!',

        // Birthday Massacre
        'I know you\'re just pretending; there\'s no window for escape. I know you see right through me; there\'s no promise left to brea-eak.',
        'Nails clawing splinters from the ceiling and floor... shrieking like the witches \'til his stitches are sore...',

        // Qemists, S.W.A.G.
        'Don\'t hide the feeling deep inside - take it in your stride, at least you know you tried. Jump back and get back a little more.',

        // Linkin Park, Burn it Down
        'The colors confliiiiiiiiiicteeeed, as the flames climbed into the clouds...',

        // Jayme Gutierrez, Like a Tiger
        'And now it sounds like I\'m straining my voice, but I\'m only really singing very soooftly. I sing the melody right over the beat to distract from any monotonicality-y.',

        // Junkie XL, Beauty Never Fades
        'Each step I take the shadows grow looongerrr... padded footfalls in the dark I waaanderrr...',

        // GitHub CoPilot??
        'I\'m not afraid of the future; I\'m not afraid of the past. I\'m not afraid of letting go, and letting my illusions last.',

        // Imogen Heap, Hide and Seek
        'Ransom notes keep falling out your mouth mid-sweet talk, newspaper word cut-outs...',

        // Darren Korb, Setting Sail/Coming Home
        'I dig my hole, you build a wall. I dig my hole, you build a wall. One day that wall is gonna fa-a-all.',

        // MINMI, Song of Four Seasons
        'Haru wo tsuge! Odoridasu sansai... Satsu wo miru ugi! Nohara karakusa kawaku wa...',

        // Daft Punk
        'Music\'s got me feeling so free, we\'re gonna celebrate - celebrate and dance so free. One more time...',

        // Moldy Peaches
        'I kiss you on the brain in the shadow of a train; kiss you all starry-eyed, my body\'s swingin\' from side to side. I don\'t see what anyone can see in anyone else... bu-ut you...',

        // Sesame Street
        'One-two-three four-five, six-seven-eight nine-ten, eleven twelve... twelve!',

        // Hey Ya!, by OutKast
        'You think you\'ve got it - oh, you think you\'ve got it - but "got it" just don\'t get it when there\'s nothin\'t at aaa a-aaa a-aaa a-aaa a-a-all!',

        // Steven Universe
        'The odds are against us, it won\'t be easy, but: we\'re not gonna do it alone!',

        // Out of my Mind, by Jamie Berry
        'I keep thinkin\' \'bou-- \'bou-- \'bou-- \'bou-- \'bou-- \'bou-- out of my mi-- mi-- mi-- mi-- mi-- mi-- I keep thinkin\'!',

        // Truly, by Delerium
        'So truuuuuuly, if there is liiight then I wanna see-ee-ee i-i-it... nooowww that I know what I am lookin\' fooorrr...',

        // Danger! (High Voltage)
        'Danger! Danger! High voltage! When we touch; when we kiss; when we touch; when we kiss!!',

        // Ciao, Ciao
        'Con le mani, con le mani, con le mani, ciao-ciao! Con i piedi, con i piedi, con i piedi, ciao-ciao!',

        // High, by Polygon
        'Answers... passing by. Lasers... super-fly. Question... question-mark. Dot, dot, dot, dot...',

        // Paper Booklet, by Pola & Bryson
        'Bam, boom-boom-boom-boom... ... ... ... *clap* *clap* *clap* *clap* *clap* *clap* *clap* *clap*... baaaam, boooooom-boom!',

        // We Love, by Ramses B
        'We love. We-- ah, we love (we love). We (we) love-- ah, we love (love)... we-- we... we love... (\'cause you know how... \'cause you know how...)',

        // Time, by Jungle
        'Say it again! Ooooooh, just hold on tight. Don\'t let it in. Yeeeaaahhh, I\'ll run all night - don\'t let me!',

        // Razor Sharp
        'Unh! ... Unh! ... ... RAZOR SHARP!',

        // Under the Sun, by Seba
        'We are the stars under the Suuuuuuuuuuun... riding the wave of life as one; taking our time to feel the love.',

        // Wash My Hands, by Kormac
        'Gonna wash my haaands of you... wash my haaands of you... when you\'ve got me in your power, your kisses turn all sour, oh! I\'m gonna wash my hands... of you...',

        // I Am Not a Robot, by Marina and the Diamonds
        'It\'s okay to say you\'ve got a weak spot. You don\'t always have to be. on. top... Better to be haaated... then lo-o-oved for what you\'re not.',

        // No Doubt
        'The waves keep on crashing on me for some reason... but your love keeps on coming like a thunderbolt...',

        // 6 Underground, by Sneaker Pimps
        'Overgrooouuund... watch this spaaaace... I\'m opeeen... to fallin\' from gra-ace...',

        // Dumb Ways to Die
        'Get your toast out... with a fork. Do your ooown electrical work. Teach yourself how to flyyy... eat a two-week-old unrefrigerated pie...',
    ];
}
