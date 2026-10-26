// Vitalis PharmaSys - UI & 3D Engine

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initAppLauncher();
  initMobileMenu();
  initPharmacy3D();
});

// 1. Manejo de Modo Claro / Modo Oscuro
function initTheme() {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

  if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }

  const toggleButtons = document.querySelectorAll('[data-toggle-theme]');
  toggleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateThemeIcons();
    });
  });

  updateThemeIcons();
}

function updateThemeIcons() {
  const isDark = document.documentElement.classList.contains('dark');
  document.querySelectorAll('[data-icon-theme-sun]').forEach(el => {
    el.style.display = isDark ? 'block' : 'none';
  });
  document.querySelectorAll('[data-icon-theme-moon]').forEach(el => {
    el.style.display = isDark ? 'none' : 'block';
  });
}

// 2. Lanzador de Aplicaciones (9 Puntos)
function initAppLauncher() {
  const trigger = document.getElementById('app-launcher-btn');
  const modal = document.getElementById('app-launcher-modal');

  if (!trigger || !modal) return;

  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    const isHidden = modal.classList.contains('hidden');
    if (isHidden) {
      modal.classList.remove('hidden');
      trigger.setAttribute('aria-expanded', 'true');
    } else {
      modal.classList.add('hidden');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });

  // Cerrar al hacer clic fuera o presionar Escape
  document.addEventListener('click', (e) => {
    if (!modal.contains(e.target) && !trigger.contains(e.target)) {
      modal.classList.add('hidden');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      modal.classList.add('hidden');
      trigger.setAttribute('aria-expanded', 'false');
    }
  });
}

// 3. Menú Móvil para Portal Público
function initMobileMenu() {
  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileNav = document.getElementById('mobile-nav');

  if (!menuBtn || !mobileNav) return;

  menuBtn.addEventListener('click', () => {
    mobileNav.classList.toggle('hidden');
  });
}

// 4. Visor 3D Inmersivo Profesional (Cápsula / Molécula Farmacéutica WebGL)
function initPharmacy3D() {
  const container = document.getElementById('pharmacy-3d-canvas');
  if (!container || typeof THREE === 'undefined') return;

  const width = container.clientWidth;
  const height = container.clientHeight || 420;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
  camera.position.z = 7;

  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(width, height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.innerHTML = '';
  container.appendChild(renderer.domElement);

  // Iluminación clínica de estudio
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.85);
  scene.add(ambientLight);

  const dirLight = new THREE.DirectionalLight(0xffffff, 0.9);
  dirLight.position.set(5, 8, 5);
  scene.add(dirLight);

  const backLight = new THREE.DirectionalLight(0xbfdbf7, 0.4);
  backLight.position.set(-5, -5, -3);
  scene.add(backLight);

  // Grupo principal
  const group = new THREE.Group();
  scene.add(group);

  // 1. Cápsula Farmacéutica: Mitad Superior (Teal #1F7A8C)
  const capTopGeo = new THREE.CylinderGeometry(1.0, 1.0, 1.2, 32);
  const sphereTopGeo = new THREE.SphereGeometry(1.0, 32, 16, 0, Math.PI * 2, 0, Math.PI / 2);

  const tealMaterial = new THREE.MeshStandardMaterial({
    color: 0x1f7a8c,
    roughness: 0.25,
    metalness: 0.1,
  });

  const whiteMaterial = new THREE.MeshStandardMaterial({
    color: 0xffffff,
    roughness: 0.2,
    metalness: 0.05,
  });

  const capTopMesh = new THREE.Mesh(capTopGeo, tealMaterial);
  capTopMesh.position.y = 0.6;
  group.add(capTopMesh);

  const sphereTopMesh = new THREE.Mesh(sphereTopGeo, tealMaterial);
  sphereTopMesh.position.y = 1.2;
  group.add(sphereTopMesh);

  // Mitad Inferior (Blanco Puro #FFFFFF con tinte suave)
  const capBottomGeo = new THREE.CylinderGeometry(1.0, 1.0, 1.2, 32);
  const sphereBottomGeo = new THREE.SphereGeometry(1.0, 32, 16, 0, Math.PI * 2, Math.PI / 2, Math.PI / 2);

  const capBottomMesh = new THREE.Mesh(capBottomGeo, whiteMaterial);
  capBottomMesh.position.y = -0.6;
  group.add(capBottomMesh);

  const sphereBottomMesh = new THREE.Mesh(sphereBottomGeo, whiteMaterial);
  sphereBottomMesh.position.y = -1.2;
  group.add(sphereBottomMesh);

  // Anillo divisor central (Petróleo #022B3A)
  const ringGeo = new THREE.TorusGeometry(1.02, 0.03, 16, 64);
  const ringMat = new THREE.MeshStandardMaterial({ color: 0x022b3a, roughness: 0.4 });
  const ringMesh = new THREE.Mesh(ringGeo, ringMat);
  ringMesh.rotation.x = Math.PI / 2;
  group.add(ringMesh);

  // 2. Anillo orbital molecular sutil (Pale Sky #BFDBF7)
  const orbitGeo = new THREE.TorusGeometry(2.3, 0.02, 16, 100);
  const orbitMat = new THREE.MeshBasicMaterial({ color: 0xbfdbf7, transparent: true, opacity: 0.65 });
  const orbitMesh = new THREE.Mesh(orbitGeo, orbitMat);
  orbitMesh.rotation.x = Math.PI / 3;
  group.add(orbitMesh);

  // Esferas satélite (átomos de trazabilidad)
  const atomGeo = new THREE.SphereGeometry(0.12, 16, 16);
  const atomMat1 = new THREE.MeshStandardMaterial({ color: 0x1f7a8c });
  const atomMesh1 = new THREE.Mesh(atomGeo, atomMat1);
  atomMesh1.position.set(2.3, 0, 0);
  orbitMesh.add(atomMesh1);

  const atomMat2 = new THREE.MeshStandardMaterial({ color: 0x022b3a });
  const atomMesh2 = new THREE.Mesh(atomGeo, atomMat2);
  atomMesh2.position.set(-2.3, 0, 0);
  orbitMesh.add(atomMesh2);

  // Inclinación inicial estética de la cápsula
  group.rotation.z = Math.PI / 6;
  group.rotation.x = Math.PI / 10;

  // Interacción suave con el cursor
  let mouseX = 0;
  let mouseY = 0;
  let targetRotationX = group.rotation.x;
  let targetRotationY = group.rotation.y;

  window.addEventListener('mousemove', (e) => {
    const rect = container.getBoundingClientRect();
    if (e.clientY >= rect.top && e.clientY <= rect.bottom) {
      mouseX = (e.clientX - rect.left - rect.width / 2) / (rect.width / 2);
      mouseY = (e.clientY - rect.top - rect.height / 2) / (rect.height / 2);
      targetRotationY = mouseX * 0.5;
      targetRotationX = (Math.PI / 10) + (mouseY * 0.4);
    }
  });

  // Bucle de animación
  function animate() {
    requestAnimationFrame(animate);

    // Rotación constante suave
    group.rotation.y += 0.006;
    orbitMesh.rotation.z -= 0.008;

    // Suavizado de seguimiento del cursor (lerp)
    group.rotation.x += (targetRotationX - group.rotation.x) * 0.05;

    renderer.render(scene, camera);
  }

  animate();

  // Redimensionado responsivo
  window.addEventListener('resize', () => {
    const newWidth = container.clientWidth;
    const newHeight = container.clientHeight || 420;
    camera.aspect = newWidth / newHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(newWidth, newHeight);
  });
}
