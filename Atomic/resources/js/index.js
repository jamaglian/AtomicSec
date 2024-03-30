const canvas = document.getElementById('binaryCanvas');
const ctx = canvas.getContext('2d');

// Set canvas dimensions to match window size
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

// Generate random binary digit
function generateBinaryDigit() {
    return Math.random() > 0.5 ? '1' : '0';
}

// Generate random binary string
function generateBinaryString(length) {
    let binaryString = '';
    for (let i = 0; i < length; i++) {
        binaryString += generateBinaryDigit();
    }
    return binaryString;
}

// Create binary raindrop object
function createRaindrop() {
    const speed = Math.random() * 5 + 1; // Random speed
    const length = Math.random() * 400 + 10; // Random length
    const x = Math.random() * canvas.width; // Random horizontal position
    const y = -length; // Start above the canvas
    const binaryString = generateBinaryString(Math.ceil(length / 10)); // Generate binary string
    return { x, y, speed, length, binaryString };
}

const raindrops = [];

// Initialize raindrops
for (let i = 0; i < 50; i++) {
    raindrops.push(createRaindrop());
}

function draw() {
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw raindrops
    for (const raindrop of raindrops) {
        ctx.fillStyle = 'red';//lime
        ctx.font = '10px monospace';
        ctx.fillText(raindrop.binaryString, raindrop.x, raindrop.y);
        raindrop.y += raindrop.speed;
        // Reset raindrop position if it goes beyond the canvas
        if (raindrop.y > canvas.height) {
            Object.assign(raindrop, createRaindrop());
        }
    }
}

function animate() {
    draw();
    requestAnimationFrame(animate);
}

animate();

// Update canvas dimensions on window resize
window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});