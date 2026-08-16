<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('The Craft of Denim — Rando Jeans')] class extends Component {
};

?>

<div class="mx-auto max-w-5xl px-6 py-16">

    <h1 class="font-display text-4xl mb-2">The Craft of Denim</h1>
    <p class="text-neutral-400 mb-12">Everything that goes into a pair of jeans, and everything we think about when we make ours.</p>

    <nav class="border border-neutral-800 p-6 mb-16 font-mono text-xs">
        <p class="uppercase tracking-widest text-neutral-500 mb-4">Contents</p>
        <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-neutral-400">
            <a href="#origins" class="hover:text-copper-light">01. Origins of Denim</a>
            <a href="#birth" class="hover:text-copper-light">02. Birth of the Modern Jean</a>
            <a href="#indigo" class="hover:text-copper-light">03. Indigo</a>
            <a href="#made" class="hover:text-copper-light">04. How Denim Is Made</a>
            <a href="#selvedge" class="hover:text-copper-light">05. Selvedge Denim</a>
            <a href="#japan" class="hover:text-copper-light">06. Japanese Denim</a>
            <a href="#america" class="hover:text-copper-light">07. American Heritage</a>
            <a href="#culture" class="hover:text-copper-light">08. A Cultural Symbol</a>
            <a href="#world" class="hover:text-copper-light">09. Denim Around the World</a>
            <a href="#weight" class="hover:text-copper-light">10. Understanding Weight</a>
            <a href="#texture" class="hover:text-copper-light">11. Texture & Character</a>
            <a href="#raw" class="hover:text-copper-light">12. Raw, Rigid & Washed</a>
            <a href="#sanforized" class="hover:text-copper-light">13. Sanforized vs Unsanforized</a>
            <a href="#black" class="hover:text-copper-light">14. Black Denim</a>
            <a href="#aging" class="hover:text-copper-light">15. How Black Jeans Age</a>
            <a href="#anatomy" class="hover:text-copper-light">16. Anatomy of Premium Jeans</a>
            <a href="#fit" class="hover:text-copper-light">17. Fit & Silhouette</a>
            <a href="#boots" class="hover:text-copper-light">18. Jeans + Boots</a>
            <a href="#premium" class="hover:text-copper-light">19. What Makes Jeans Premium</a>
            <a href="#philosophy" class="hover:text-copper-light">20. Our Denim Philosophy</a>
        </div>
    </nav>

    <div class="space-y-16 leading-relaxed text-neutral-300">

        <section id="origins">
            <h2 class="font-display text-2xl text-bone mb-3">01. Origins of Denim</h2>
            <p>The word "denim" traces back to <em>serge de Nîmes</em> — a sturdy twill fabric from Nîmes, France. Early versions were coarse, indigo-dyed work cloth, worn for durability rather than style. Modern denim shares that twill structure but is spun, woven, and finished with far more consistency and control than the original 18th- and 19th-century workwear fabrics it descended from.</p>
        </section>

        <section id="birth">
            <h2 class="font-display text-2xl text-bone mb-3">02. Birth of the Modern Jean</h2>
            <p>The jean as we know it began in 1873, when Levi Strauss and tailor Jacob Davis patented copper-riveted work trousers. The rivets reinforced stress points — pocket corners, fly seams — that kept tearing under hard labour. Their first customers were miners, railroad workers, and labourers during the American westward expansion. Jeans were workwear, full stop; nobody wore them for fashion for another seventy-odd years.</p>
        </section>

        <section id="indigo">
            <h2 class="font-display text-2xl text-bone mb-3">03. Indigo — The Colour of Denim</h2>
            <p>Natural indigo comes from the <em>Indigofera</em> plant, used as a dye for centuries before synthetic indigo was developed in the 1880s–90s, making the colour affordable at industrial scale. Indigo behaves unlike most dyes: it doesn't fully penetrate the yarn, instead coating the outer layer and leaving a white core. That's why denim fades unevenly with wear — you're gradually exposing that white core through friction. Rope dyeing and slasher dyeing are the two dominant methods, producing slightly different fade characters.</p>
        </section>

        <section id="made">
            <h2 class="font-display text-2xl text-bone mb-3">04. How Denim Is Made</h2>
            <p>Cotton is spun into yarn, then split into two roles: the warp (lengthwise yarns, dyed indigo) and the weft (crosswise yarns, usually left undyed white). Weaving interlaces them — classic denim uses a 3×1 right-hand twill, giving the fabric its diagonal grain; left-hand twill and broken twill produce different textures and drape. After weaving, the fabric is finished and often sanforized (pre-shrunk) so it behaves predictably once sewn into a garment.</p>
        </section>

        <section id="selvedge">
            <h2 class="font-display text-2xl text-bone mb-3">05. Selvedge Denim</h2>
            <p>"Selvedge" (self-edge) refers to the tightly woven, self-finished edge of narrow-width fabric produced on vintage shuttle looms — visible as a clean coloured line inside a jean's cuff. Shuttle looms produce narrower fabric far more slowly than modern projectile looms, which is why selvedge became associated with craftsmanship and small-batch production. Selvedge isn't automatically higher quality than non-selvedge — but it does signal a slower, more traditional weaving process.</p>
        </section>

        <section id="japan">
            <h2 class="font-display text-2xl text-bone mb-3">06. Japanese Denim</h2>
            <p>Japan's denim reputation stems from Okayama, particularly the Kojima district, where mills kept vintage shuttle looms running long after most of the world moved on. Japanese makers essentially reverse-engineered vintage American workwear with obsessive attention to texture and dye — producing denim known for irregular, characterful surfaces. If you ever source Japanese fabric, this heritage is a genuine, defensible story to tell.</p>
        </section>

        <section id="america">
            <h2 class="font-display text-2xl text-bone mb-3">07. American Denim Heritage</h2>
            <p>Denim is inseparable from the American West — cowboys, ranch hands, miners, and railroad workers built its early reputation for toughness. Levi's, Lee, and Wrangler each grew out of that same workwear tradition before jeans crossed over into everyday civilian clothing in the mid-20th century.</p>
        </section>

        <section id="culture">
            <h2 class="font-display text-2xl text-bone mb-3">08. Jeans Become a Cultural Symbol</h2>
            <p>Jeans stayed pure workwear through the 1930s–40s, then shifted meaning fast: 1950s youth culture (James Dean, Marlon Brando) turned them into a symbol of rebellion; rock & roll and motorcycle culture kept that association through the 60s; 60s–70s counterculture and later punk pushed them further from their origins; eventually designer denim brought jeans into luxury fashion, and the current heritage/workwear revival — where this brand sits — looks back toward where it all started.</p>
        </section>

        <section id="world">
            <h2 class="font-display text-2xl text-bone mb-3">09. Denim Around the World</h2>
            <p>The US and Japan are the most storied denim traditions, but Italy (fashion-forward finishing and washes), Turkey (large-scale quality manufacturing), China, and Vietnam are all major modern denim producers, each with different strengths — from mass-scale efficiency to specialised finishing techniques.</p>
        </section>

        <section id="weight">
            <h2 class="font-display text-2xl text-bone mb-3">10. Understanding Denim Weight</h2>
            <p>Denim weight is measured in ounces per square yard (oz/yd²). Roughly: lightweight is under 12oz, midweight 12–14oz, heavyweight 14–16oz, and anything above 16oz is considered very heavy. Heavier denim generally drapes stiffer, takes longer to break in, and lasts longer — but heavier isn't automatically better; it's a trade-off between structure, comfort, and climate suitability.</p>
        </section>

        <section id="texture">
            <h2 class="font-display text-2xl text-bone mb-3">11. Denim Texture & Character</h2>
            <p>Slub (irregular yarn thickness), nep (small fibre knots), and general yarn "hairiness" all come from looser spinning and looser loom tension — deliberately used to create vintage-style, textured denim rather than a smooth, uniform surface. This texture becomes more pronounced with wear as the surface fibres abrade.</p>
        </section>

        <section id="raw">
            <h2 class="font-display text-2xl text-bone mb-3">12. Raw, Rigid & Washed Denim</h2>
            <p>Raw (or rigid) denim is unwashed after dyeing and weaving — it fades naturally and uniquely to the wearer's body and habits over months of wear. Everything else on the spectrum — one-wash, rinsed, stone wash, enzyme wash, garment wash, resin finishes — pre-fades the fabric artificially to varying degrees, trading that personal, earned fade for an immediately "worn-in" look.</p>
        </section>

        <section id="sanforized">
            <h2 class="font-display text-2xl text-bone mb-3">13. Sanforized vs Unsanforized</h2>
            <p>Sanforization is a mechanical pre-shrinking process — sanforized denim shrinks minimally after purchase, so sizing stays predictable. Unsanforized denim can shrink significantly (often "shrink-to-fit," worn once and soaked to mould to the body) — some enthusiasts prefer this for the more personalised fit it eventually produces, but sanforized denim is the practical, predictable choice for most customers.</p>
        </section>

        <section id="black">
            <h2 class="font-display text-2xl text-bone mb-3">14. Black Denim</h2>
            <p>Black jeans followed indigo denim's construction but swapped the dye — typically sulfur black (deep, relatively stable) or reactive/pigment dyes (which sit more on the surface and fade faster, often toward grey rather than the blue-white core fade of indigo). Warp and weft can both be dyed black for a deeper, more colourfast result, or just the warp for a more traditional denim structure. "True black" fades toward charcoal and grey with wear rather than blue — a distinct, deliberate aging character that's central to what makes black denim visually different from indigo.</p>
        </section>

        <section id="aging">
            <h2 class="font-display text-2xl text-bone mb-3">15. How Black Jeans Age</h2>
            <p>A fresh pair starts jet or deep charcoal black. With wear, stress points fade first and most visibly: whiskers (creases across the upper thigh), honeycombs (behind the knee), stack fades (bunching at the ankle), and pocket/edge abrasion. Over months, the whole garment gradually shifts from black toward soft charcoal-grey — a highly individual pattern shaped entirely by how each person actually wears and moves in the jeans.</p>
        </section>

        <section id="anatomy">
            <h2 class="font-display text-2xl text-bone mb-3">16. Anatomy of Premium Jeans</h2>
            <p>Beyond the fabric itself: rivets and bar tacks reinforce stress points, felled seams resist blowout, chain-stitched hems hang with a slight roped edge as they age, quality pocket bags and coin pockets add durability where it's invisible but felt, and details like the leather patch, button/zipper choice, and waistband construction all separate a genuinely well-built pair from one that just looks the part.</p>
        </section>

        <section id="fit">
            <h2 class="font-display text-2xl text-bone mb-3">17. Fit & Silhouette</h2>
            <p>Fit is a combination of rise, thigh room, knee width, hem opening, and seat — and how those proportions relate to each other determines whether a jean reads as regular straight, relaxed straight, slim, or something else entirely. Drape and whether it's meant to stack or break cleanly at the shoe are deliberate design decisions, not accidents.</p>
        </section>

        <section id="boots">
            <h2 class="font-display text-2xl text-bone mb-3">18. Jeans + Boots</h2>
            <p>Hem opening matters enormously once boots enter the picture — too narrow and a jean won't sit over a service or work boot properly; too wide and it loses shape. Heavier denim holds a cleaner line over boot shafts than lightweight fabric. Cuffing style (single cuff, double cuff, or no cuff at all) changes the proportion between boot shaft and visible leg — worth designing around deliberately if boots are part of your customer's actual wardrobe.</p>
        </section>

        <section id="premium">
            <h2 class="font-display text-2xl text-bone mb-3">19. What Actually Makes Jeans Premium</h2>
            <p>"Premium" shouldn't just be a marketing word — it should be traceable to specific decisions: fibre and yarn quality, dyeing method, weaving process, fabric weight and consistency, precision in cutting and pattern development, stitch density, thread and hardware quality, pocketing, construction technique, finishing, and consistent quality control. Being able to name these specifically — rather than just asserting "premium" — is what actually builds customer trust.</p>
        </section>

        <section id="philosophy" class="border-t border-dashed border-neutral-700 pt-16">
            <h2 class="font-display text-2xl text-bone mb-3">20. Our Denim Philosophy</h2>
            <p><strong class="text-bone">Why black?</strong> Because it's the one colour that goes from workwear to formal without changing, and doesn't chase seasonal fade trends the way indigo washes do.</p>
            <p class="mt-4"><strong class="text-bone">Why heavier denim?</strong> [Fill in once fabric weight is chosen.]</p>
            <p class="mt-4"><strong class="text-bone">Why 100% cotton?</strong> [If confirmed — natural fibre breathability and a more authentic aging pattern.]</p>
            <p class="mt-4"><strong class="text-bone">Why designed around boots?</strong> Because Australians who actually work or ride in denim need jeans that function with real footwear, not just look good on a hanger.</p>
            <p class="mt-4"><strong class="text-bone">Why manufacture in Vietnam?</strong> [Fill in once your supplier is confirmed.]</p>
            <p class="mt-4"><strong class="text-bone">Why should these jeans last for years, not one season?</strong> Because that's the actual promise of "premium" — not a marketing word, but a jean you don't replace every year.</p>
        </section>

    </div>
</div>