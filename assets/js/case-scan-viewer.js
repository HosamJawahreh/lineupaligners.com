import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { STLLoader } from 'three/addons/loaders/STLLoader.js';
import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';
import { MTLLoader } from 'three/addons/loaders/MTLLoader.js';
import { PLYLoader } from 'three/addons/loaders/PLYLoader.js';

const root = document.getElementById('case-scan-viewer-root');
const canvas = document.getElementById('case-scan-canvas');

if (root && canvas) {
    let scanSets = [];
    try {
        scanSets = JSON.parse(root.dataset.scanSets || '[]');
    } catch (err) {
        console.error('case-scan-viewer: invalid scan sets', err);
    }

    let scans = [];
    try {
        scans = JSON.parse(root.dataset.scans || '[]');
    } catch (err) {
        console.error('case-scan-viewer: invalid scan data', err);
    }

    const scanSetSelect = document.getElementById('case-scan-set-select');
    const modNotesEl = document.getElementById('case-scan-mod-notes');
    const modNotesText = document.getElementById('case-scan-mod-notes-text');
    const filesListEl = document.getElementById('case-scan-files-list');
    const filesPanelEl = document.getElementById('case-scan-files-panel');
    const upperPlaceholderUrl = root.dataset.upperPlaceholder || '';
    const lowerPlaceholderUrl = root.dataset.lowerPlaceholder || '';
    const canvasWrap = document.getElementById('case-scan-canvas-wrap');
    const viewerPane = document.getElementById('case-scan-viewer-pane');
    const toolbar = root.querySelector('.case-scan-toolbar');
    const loadingEl = document.getElementById('case-scan-loading');
    const emptyEl = document.getElementById('case-scan-empty');
    const emptyText = document.getElementById('case-scan-empty-text');
    const errorEl = document.getElementById('case-scan-error');
    const errorText = document.getElementById('case-scan-error-text');
    const loadingText = document.getElementById('case-scan-loading-text');

    const EMPTY_SCAN_SET_MESSAGE = 'No 3D scan files in this scan version. Switch scan version or upload scans when editing the case.';

    const SCAN_STYLES = {
        upper: { color: 0xf8fafc, label: 'Upper' },
        lower: { color: 0xe2e8f0, label: 'Lower' },
    };

    /** >1 pulls camera back so models appear smaller on screen */
    const CAMERA_DISTANCE_FACTOR = 1.55;

    const BG_COLORS_LIGHT = [0xb8c5d4, 0xe2e8f0, 0xf8fafc, 0x1e293b];
    const BG_COLORS_DARK = [0x0c1117, 0x161b22, 0x1c2330, 0x0f1623];
    const GRID_COLORS_LIGHT = { center: 0x64748b, grid: 0x94a3b8 };
    const GRID_COLORS_DARK = { center: 0x3d4f63, grid: 0x243044 };
    const LIGHT_LEVELS = [0.55, 0.9, 1.3];

    function isDarkMode() {
        return document.body.classList.contains('lineup-color-dark');
    }

    function getBgColors() {
        return isDarkMode() ? BG_COLORS_DARK : BG_COLORS_LIGHT;
    }

    function getGridColors() {
        return isDarkMode() ? GRID_COLORS_DARK : GRID_COLORS_LIGHT;
    }

    function getDefaultBgIndex() {
        return 0;
    }

    const viewerState = {
        wireframe: false,
        flatShading: false,
        showAxes: false,
        showGrid: true,
        autoRotate: false,
        moveMode: false,
        panEnabled: true,
        bgIndex: 0,
        lightIndex: 1,
    };

    let axesHelper = null;
    let gridHelper = null;
    let layoutSnapshot = null;
    let autoRotateBeforeMove = false;
    let selectedScanId = null;

    const MOVE_LERP_DRAG = 0.55;
    const MOVE_LERP_IDLE = 0.25;

    const modelDrag = {
        active: false,
        scanId: null,
        pointerId: null,
        start: new THREE.Vector2(),
        wrapperStart: new THREE.Vector3(),
        target: new THREE.Vector3(),
    };
    const dragVectors = {
        right: new THREE.Vector3(),
        up: new THREE.Vector3(),
    };
    const raycaster = new THREE.Raycaster();
    const pointerNdc = new THREE.Vector2();

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(getBgColors()[getDefaultBgIndex()]);

    const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 8000);
    camera.position.set(0, 40, 180);

    const renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: true,
        alpha: false,
        preserveDrawingBuffer: true,
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const controls = new OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.dampingFactor = 0.06;
    controls.rotateSpeed = 0.7;
    controls.panSpeed = 1.25;
    controls.screenSpacePanning = true;
    controls.enablePan = true;
    controls.enableZoom = true;

    function applyControlBindings() {
        controls.enableZoom = true;

        if (viewerState.moveMode) {
            controls.enableRotate = true;
            controls.enablePan = false;
            controls.mouseButtons = {
                LEFT: THREE.MOUSE.ROTATE,
                MIDDLE: THREE.MOUSE.DOLLY,
                RIGHT: THREE.MOUSE.ROTATE,
            };
            controls.touches = {
                ONE: THREE.TOUCH.ROTATE,
                TWO: THREE.TOUCH.DOLLY_PAN,
            };
        } else {
            controls.enableRotate = true;
            controls.enablePan = viewerState.panEnabled;
            controls.mouseButtons = {
                LEFT: THREE.MOUSE.ROTATE,
                MIDDLE: THREE.MOUSE.DOLLY,
                RIGHT: viewerState.panEnabled ? THREE.MOUSE.PAN : THREE.MOUSE.ROTATE,
            };
            controls.touches = {
                ONE: THREE.TOUCH.ROTATE,
                TWO: THREE.TOUCH.DOLLY_PAN,
            };
        }
    }

    function captureLayoutSnapshot() {
        layoutSnapshot = {
            modelGroup: modelGroup.position.clone(),
            biteGroup: biteGroup.rotation.clone(),
            wrappers: new Map(),
        };

        meshesById.forEach((entry, scanId) => {
            layoutSnapshot.wrappers.set(scanId, entry.wrapper.position.clone());
        });
    }

    function resetView() {
        if (meshesById.size === 0) {
            return;
        }

        if (!layoutSnapshot) {
            layoutMeshes();
            return;
        }

        modelGroup.position.copy(layoutSnapshot.modelGroup);
        if (layoutSnapshot.biteGroup) {
            biteGroup.rotation.copy(layoutSnapshot.biteGroup);
        }
        layoutSnapshot.wrappers.forEach((position, scanId) => {
            const entry = meshesById.get(scanId);
            if (entry) {
                entry.wrapper.position.copy(position);
            }
        });

        modelGroup.updateMatrixWorld(true);
        applyInitialCameraView();
    }

    function getScanIdFromObject(object) {
        let node = object;
        while (node) {
            if ((node.parent === modelGroup || node.parent === biteGroup) && meshesById.has(node.name)) {
                return node.name;
            }
            node = node.parent;
        }
        return null;
    }

    function pickScanAtEvent(event) {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) {
            return null;
        }

        pointerNdc.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        pointerNdc.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        raycaster.setFromCamera(pointerNdc, camera);

        const wrappers = Array.from(meshesById.values())
            .filter((entry) => entry.wrapper.visible)
            .map((entry) => entry.wrapper);

        const hits = raycaster.intersectObjects(wrappers, true);
        for (let i = 0; i < hits.length; i += 1) {
            const scanId = getScanIdFromObject(hits[i].object);
            if (scanId) {
                return scanId;
            }
        }

        return null;
    }

    function getDefaultMoveScanId() {
        const visible = getVisibleMeshEntries();
        if (!visible.length) {
            return null;
        }

        if (selectedScanId && meshesById.get(selectedScanId)?.wrapper.visible) {
            return selectedScanId;
        }

        return visible[0].wrapper.name;
    }

    function syncMoveSelectionUi() {
        root.querySelectorAll('.case-scan-file-card[data-scan-id]').forEach((card) => {
            const id = card.getAttribute('data-scan-id');
            card.classList.toggle('is-move-selected', viewerState.moveMode && id === selectedScanId);
        });
    }

    function selectScanForMove(scanId) {
        if (!scanId || !meshesById.has(scanId)) {
            return;
        }

        selectedScanId = scanId;
        const entry = meshesById.get(scanId);
        modelDrag.target.copy(entry.wrapper.position);
        syncMoveSelectionUi();
    }

    function persistWrapperPosition(scanId) {
        if (!scanId) {
            return;
        }

        const entry = meshesById.get(scanId);
        if (!entry) {
            return;
        }

        if (!layoutSnapshot) {
            captureLayoutSnapshot();
            return;
        }

        layoutSnapshot.wrappers.set(scanId, entry.wrapper.position.clone());
    }

    function setMoveDragTarget(deltaX, deltaY) {
        const rect = canvas.getBoundingClientRect();
        if (!rect.height) {
            return;
        }

        const distance = camera.position.distanceTo(controls.target);
        const scale = (distance / rect.height) * 1.2;

        camera.updateMatrixWorld(true);
        dragVectors.right.setFromMatrixColumn(camera.matrixWorld, 0);
        dragVectors.up.setFromMatrixColumn(camera.matrixWorld, 1);

        modelDrag.target.copy(modelDrag.wrapperStart);
        modelDrag.target.addScaledVector(dragVectors.right, deltaX * scale);
        modelDrag.target.addScaledVector(dragVectors.up, -deltaY * scale);
    }

    function tickMoveSmoothing() {
        const scanId = modelDrag.active ? modelDrag.scanId : selectedScanId;
        if (!viewerState.moveMode || !scanId) {
            return;
        }

        const entry = meshesById.get(scanId);
        if (!entry) {
            return;
        }

        const factor = modelDrag.active ? MOVE_LERP_DRAG : MOVE_LERP_IDLE;
        entry.wrapper.position.lerp(modelDrag.target, factor);
    }

    function initModelMoveControls() {
        canvas.addEventListener('pointerdown', (event) => {
            if (!viewerState.moveMode || event.button !== 0) {
                return;
            }

            const picked = pickScanAtEvent(event);
            if (picked) {
                selectScanForMove(picked);
            }

            const scanId = picked || getDefaultMoveScanId();
            const entry = scanId ? meshesById.get(scanId) : null;
            if (!entry || !entry.wrapper.visible) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            modelDrag.active = true;
            modelDrag.scanId = scanId;
            modelDrag.pointerId = event.pointerId;
            modelDrag.start.set(event.clientX, event.clientY);
            modelDrag.wrapperStart.copy(entry.wrapper.position);
            modelDrag.target.copy(entry.wrapper.position);
            controls.enabled = false;
            canvas.setPointerCapture(event.pointerId);
            canvasWrap?.classList.add('is-dragging-model');
        }, true);

        canvas.addEventListener('pointermove', (event) => {
            if (!modelDrag.active || event.pointerId !== modelDrag.pointerId) {
                return;
            }

            event.preventDefault();
            setMoveDragTarget(
                event.clientX - modelDrag.start.x,
                event.clientY - modelDrag.start.y
            );
        });

        const endModelDrag = (event) => {
            if (!modelDrag.active) {
                return;
            }
            if (modelDrag.pointerId !== null && event.pointerId !== modelDrag.pointerId) {
                return;
            }

            const draggedId = modelDrag.scanId;
            modelDrag.active = false;
            modelDrag.scanId = null;
            modelDrag.pointerId = null;
            controls.enabled = true;
            canvasWrap?.classList.remove('is-dragging-model');

            const entry = draggedId ? meshesById.get(draggedId) : null;
            if (entry) {
                entry.wrapper.position.copy(modelDrag.target);
                persistWrapperPosition(draggedId);
            }

            try {
                if (canvas.hasPointerCapture(event.pointerId)) {
                    canvas.releasePointerCapture(event.pointerId);
                }
            } catch (err) {
                // pointer may already be released
            }
        };

        canvas.addEventListener('pointerup', endModelDrag);
        canvas.addEventListener('pointercancel', endModelDrag);
        canvas.addEventListener('lostpointercapture', () => {
            if (modelDrag.active && modelDrag.scanId) {
                persistWrapperPosition(modelDrag.scanId);
            }
            modelDrag.active = false;
            modelDrag.scanId = null;
            modelDrag.pointerId = null;
            controls.enabled = true;
            canvasWrap?.classList.remove('is-dragging-model');
        });
    }

    function bindMoveSelectionFromFiles() {
        root.querySelectorAll('.case-scan-file-card[data-scan-id]').forEach((card) => {
            if (card.dataset.moveSelectBound === '1') {
                return;
            }
            card.dataset.moveSelectBound = '1';

            card.addEventListener('click', (event) => {
                if (!viewerState.moveMode) {
                    return;
                }
                if (event.target.closest('.case-scan-file__action')) {
                    return;
                }

                const scanId = card.getAttribute('data-scan-id');
                if (scanId) {
                    selectScanForMove(scanId);
                }
            });
        });
    }

    const ambientLight = new THREE.AmbientLight(0xffffff, 0.62);
    scene.add(ambientLight);
    const keyLight = new THREE.DirectionalLight(0xffffff, 0.9);
    keyLight.position.set(2, 3, 4);
    scene.add(keyLight);
    const fillLight = new THREE.DirectionalLight(0xe8f4fc, 0.5);
    fillLight.position.set(-2, 0, -2);
    scene.add(fillLight);

    const modelGroup = new THREE.Group();
    scene.add(modelGroup);

    /** Holds upper+lower together — rotate as one unit (Trimesh-style). */
    const biteGroup = new THREE.Group();
    biteGroup.name = 'bite-pair';
    modelGroup.add(biteGroup);

    const meshesById = new Map();
    let animating = true;
    let loadedCount = 0;

    function materialFor(scanId) {
        const style = SCAN_STYLES[scanId] || { color: 0xf8fafc };
        return new THREE.MeshPhongMaterial({
            color: style.color,
            specular: 0x333333,
            shininess: 40,
            flatShading: false,
            side: THREE.DoubleSide,
        });
    }

    function vertexColorMaterial(geometry) {
        const material = new THREE.MeshStandardMaterial({
            color: 0xffffff,
            vertexColors: true,
            metalness: 0.04,
            roughness: 0.62,
            flatShading: false,
            side: THREE.DoubleSide,
        });

        if (geometry?.alpha !== undefined && geometry.alpha < 1) {
            material.transparent = true;
            material.opacity = geometry.alpha;
        }

        return material;
    }

    function geometryHasVertexColors(geometry) {
        if (!geometry) {
            return false;
        }

        if (geometry.hasColors) {
            return true;
        }

        const colors = geometry.attributes?.color;
        return Boolean(colors && colors.count > 0);
    }

    function isDefaultMaterialColor(color) {
        if (!color) {
            return true;
        }
        return color.r >= 0.92 && color.g >= 0.92 && color.b >= 0.92;
    }

    function materialHasFileColor(material) {
        if (!material) {
            return false;
        }
        if (material.vertexColors || material.map) {
            return true;
        }
        return !isDefaultMaterialColor(material.color);
    }

    function meshHasFileColors(mesh) {
        if (!mesh) {
            return false;
        }
        if (geometryHasVertexColors(mesh.geometry)) {
            return true;
        }
        const materials = Array.isArray(mesh.material) ? mesh.material : [mesh.material];
        return materials.some((material) => materialHasFileColor(material));
    }

    function objectHasFileColors(object) {
        let found = false;
        object.traverse((child) => {
            if (found || !child.isMesh) {
                return;
            }
            if (meshHasFileColors(child)) {
                found = true;
            }
        });
        return found;
    }

    function disposeMaterial(material) {
        if (!material) {
            return;
        }
        if (Array.isArray(material)) {
            material.forEach((m) => m.dispose());
        } else {
            material.dispose();
        }
    }

    function enhanceLoadedMaterial(material) {
        if (!material) {
            return null;
        }

        if (materialHasFileColor(material)) {
            material.side = THREE.DoubleSide;

            if (material.map) {
                material.map.colorSpace = THREE.SRGBColorSpace;
            }

            if (material.isMeshStandardMaterial) {
                return material;
            }

            if (material.isMeshPhongMaterial || material.isMeshLambertMaterial) {
                const enhanced = new THREE.MeshStandardMaterial({
                    color: material.color ? material.color.clone() : 0xffffff,
                    map: material.map || null,
                    normalMap: material.normalMap || null,
                    vertexColors: Boolean(material.vertexColors),
                    metalness: 0.04,
                    roughness: 0.62,
                    transparent: material.transparent,
                    opacity: material.opacity,
                    side: THREE.DoubleSide,
                });
                material.dispose();
                return enhanced;
            }

            if (material.color || material.map) {
                const enhanced = new THREE.MeshStandardMaterial({
                    color: material.color ? material.color.clone() : 0xffffff,
                    map: material.map || null,
                    vertexColors: Boolean(material.vertexColors),
                    metalness: 0.04,
                    roughness: 0.62,
                    side: THREE.DoubleSide,
                });
                material.dispose();
                return enhanced;
            }
        }

        return null;
    }

    function applyDefaultMaterial(mesh, scanId) {
        const mat = materialFor(scanId);
        mesh.traverse((child) => {
            if (child.isMesh) {
                disposeMaterial(child.material);
                child.material = mat;
            }
        });
    }

    /**
     * Use colors/textures from the file when present; otherwise aligner defaults (upper/lower).
     * @returns {boolean} whether file colors were kept
     */
    function prepareObjectMaterials(object, scanId) {
        if (!objectHasFileColors(object)) {
            applyDefaultMaterial(object, scanId);
            return false;
        }

        object.traverse((child) => {
            if (!child.isMesh) {
                return;
            }

            if (geometryHasVertexColors(child.geometry)) {
                disposeMaterial(child.material);
                child.material = vertexColorMaterial(child.geometry);
                return;
            }

            const materials = Array.isArray(child.material) ? child.material : [child.material];
            const next = materials.map((material) => {
                return enhanceLoadedMaterial(material) || materialFor(scanId);
            });

            if (materials.some((material, index) => material !== next[index])) {
                disposeMaterial(child.material);
            }

            child.material = Array.isArray(child.material) ? next : next[0];
        });

        return true;
    }

    function createPlyLoader() {
        const loader = new PLYLoader();
        loader.setPropertyNameMapping({
            diffuse_red: 'red',
            diffuse_green: 'green',
            diffuse_blue: 'blue',
            f_red: 'red',
            f_green: 'green',
            f_blue: 'blue',
            red: 'red',
            green: 'green',
            blue: 'blue',
        });
        return loader;
    }

    function setOverlay(state, message) {
        loadingEl.classList.toggle('is-hidden', state !== 'loading');
        if (emptyEl) {
            emptyEl.classList.toggle('is-hidden', state !== 'empty');
        }
        errorEl.classList.toggle('is-hidden', state !== 'error');
        if (message && loadingText) {
            loadingText.textContent = message;
        }
        if (message && emptyText && state === 'empty') {
            emptyText.textContent = message;
        }
        if (message && errorText && state === 'error') {
            errorText.textContent = message;
        }
    }

    function resize() {
        const wrap = canvas.parentElement;
        if (!wrap) {
            return false;
        }

        let width = wrap.clientWidth;
        let height = wrap.clientHeight;

        if (height < 1 && isViewerFullscreen()) {
            const toolbarWrap = root.querySelector('.case-scan-toolbar-wrap');
            const toolbarHeight = toolbarWrap ? toolbarWrap.offsetHeight : 0;
            width = Math.max(root.clientWidth || window.innerWidth, 1);
            height = Math.max((root.clientHeight || window.innerHeight) - toolbarHeight, 1);
        }

        if (width < 1 || height < 1) {
            return false;
        }

        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height, false);
        return true;
    }

    function afterLayout(callback) {
        requestAnimationFrame(() => {
            requestAnimationFrame(callback);
        });
    }

    function disposeObject(object) {
        object.traverse((child) => {
            if (child.geometry) {
                child.geometry.dispose();
            }
            if (child.material) {
                if (Array.isArray(child.material)) {
                    child.material.forEach((m) => m.dispose());
                } else {
                    child.material.dispose();
                }
            }
        });
    }

    function forEachMesh(callback) {
        meshesById.forEach((entry) => {
            entry.wrapper.traverse((child) => {
                if (child.isMesh && child.material) {
                    callback(child);
                }
            });
        });
    }

    function forEachMeshMaterial(mesh, callback) {
        const materials = Array.isArray(mesh.material) ? mesh.material : [mesh.material];
        materials.forEach((material) => {
            if (material) {
                callback(material);
            }
        });
    }

    function applyWireframe() {
        forEachMesh((mesh) => {
            forEachMeshMaterial(mesh, (material) => {
                material.wireframe = viewerState.wireframe;
                material.needsUpdate = true;
            });
        });
    }

    function applyFlatShading() {
        forEachMesh((mesh) => {
            forEachMeshMaterial(mesh, (material) => {
                material.flatShading = viewerState.flatShading;
                material.needsUpdate = true;
            });
        });
    }

    function setPanEnabled(on) {
        viewerState.panEnabled = on;
        applyControlBindings();
        setToggleActive('toggle-pan', on);
    }

    function setCameraPreset(preset) {
        const visible = getVisibleMeshEntries();
        if (!visible.length) {
            return;
        }

        modelGroup.updateMatrixWorld(true);

        const box = new THREE.Box3();
        visible.forEach((entry) => {
            box.expandByObject(entry.wrapper);
        });

        if (box.isEmpty()) {
            return;
        }

        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z, 1);
        const distance = maxDim * CAMERA_DISTANCE_FACTOR * 1.15;

        controls.target.copy(center);

        switch (preset) {
            case 'top':
                camera.position.set(center.x, center.y + distance, center.z);
                camera.up.set(0, 1, 0);
                break;
            case 'front': {
                const upper = meshesById.get('upper');
                const lower = meshesById.get('lower');
                if (upper && lower && upper.wrapper.visible && lower.wrapper.visible) {
                    applyFrontBiteCameraView();
                    return;
                }
                camera.position.set(center.x, center.y, center.z + distance);
                camera.up.set(0, 1, 0);
                break;
            }
            case 'side':
                camera.position.set(center.x + distance, center.y, center.z);
                camera.up.set(0, 1, 0);
                break;
            default:
                break;
        }

        controls.update();
    }

    function setAllModelsVisible(visible) {
        root.querySelectorAll('.case-scan-file__action--view input[data-scan-id]').forEach((input) => {
            const scanId = input.getAttribute('data-scan-id');
            if (!scanId) {
                return;
            }

            input.checked = visible;
            setScanVisibility(scanId, visible);
        });

        if (visible && getVisibleMeshEntries().length) {
            applyInitialCameraView();
        }
    }

    function getToggleBtn(tool) {
        return toolbar ? toolbar.querySelector(`[data-scan-tool="${tool}"]`) : null;
    }

    function setToggleActive(tool, active) {
        const btn = getToggleBtn(tool);
        if (!btn) {
            return;
        }
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    function syncBackground() {
        const palette = getBgColors();
        if (viewerState.bgIndex >= palette.length) {
            viewerState.bgIndex = getDefaultBgIndex();
        }
        const hex = palette[viewerState.bgIndex];
        scene.background = new THREE.Color(hex);
        const css = '#' + hex.toString(16).padStart(6, '0');
        if (canvasWrap) {
            canvasWrap.style.background = css;
        }
        if (viewerPane && !document.fullscreenElement) {
            viewerPane.style.background = css;
        }
    }

    function applyLighting() {
        const mult = LIGHT_LEVELS[viewerState.lightIndex];
        ambientLight.intensity = 0.62 * mult;
        keyLight.intensity = 0.9 * mult;
        fillLight.intensity = 0.5 * mult;
    }

    const AXIS_STYLE = {
        x: { color: 0xdc2626, label: '+X', negLabel: '−X', name: 'Right' },
        y: { color: 0x16a34a, label: '+Y', negLabel: '−Y', name: 'Up' },
        z: { color: 0x2563eb, label: '+Z', negLabel: '−Z', name: 'Forward' },
    };

    const axisUnitY = new THREE.Vector3(0, 1, 0);
    const axisAlignQuat = new THREE.Quaternion();

    function pickNiceAxisStep(rough) {
        const safe = Math.max(rough, 1);
        const magnitude = Math.pow(10, Math.floor(Math.log10(safe)));
        const normalized = safe / magnitude;
        if (normalized <= 1.5) {
            return magnitude;
        }
        if (normalized <= 3.5) {
            return 2 * magnitude;
        }
        if (normalized <= 7.5) {
            return 5 * magnitude;
        }
        return 10 * magnitude;
    }

    function computeAxesSpec() {
        const visible = getVisibleMeshEntries();
        let extent = 60;

        if (visible.length) {
            const box = new THREE.Box3();
            visible.forEach((entry) => box.expandByObject(entry.wrapper));
            const size = box.getSize(new THREE.Vector3());
            extent = Math.max(size.x, size.y, size.z, 12);
        }

        const length = extent * 0.72;
        const step = pickNiceAxisStep(length / 4);
        const tickCount = Math.max(2, Math.ceil(length / step));

        return { length, step, tickCount };
    }

    function disposeAxesHelper() {
        if (!axesHelper) {
            return;
        }

        axesHelper.traverse((child) => {
            if (child.geometry) {
                child.geometry.dispose();
            }
            const materials = child.material;
            if (!materials) {
                return;
            }
            const list = Array.isArray(materials) ? materials : [materials];
            list.forEach((material) => {
                if (material.map) {
                    material.map.dispose();
                }
                material.dispose();
            });
        });

        scene.remove(axesHelper);
        axesHelper = null;
    }

    function makeAxisLabelSprite(text, colorHex, fontSize) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return null;
        }

        const padX = 10;
        const padY = 6;
        ctx.font = `600 ${fontSize}px system-ui, -apple-system, sans-serif`;
        const textWidth = Math.ceil(ctx.measureText(text).width);
        canvas.width = textWidth + padX * 2;
        canvas.height = fontSize + padY * 2;

        ctx.font = `600 ${fontSize}px system-ui, -apple-system, sans-serif`;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.94)';
        ctx.strokeStyle = colorHex;
        ctx.lineWidth = 2;
        ctx.beginPath();
        if (typeof ctx.roundRect === 'function') {
            ctx.roundRect(1, 1, canvas.width - 2, canvas.height - 2, 5);
        } else {
            ctx.rect(1, 1, canvas.width - 2, canvas.height - 2);
        }
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = colorHex;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, canvas.width / 2, canvas.height / 2);

        const texture = new THREE.CanvasTexture(canvas);
        texture.minFilter = THREE.LinearFilter;
        const material = new THREE.SpriteMaterial({
            map: texture,
            transparent: true,
            depthTest: false,
            depthWrite: false,
        });
        const sprite = new THREE.Sprite(material);
        const aspect = canvas.width / canvas.height;
        const height = fontSize * 0.22;
        sprite.scale.set(height * aspect, height, 1);
        sprite.renderOrder = 10;
        return sprite;
    }

    function colorHexFromNumber(hex) {
        return '#' + hex.toString(16).padStart(6, '0');
    }

    function addAxisShaft(group, direction, length, color, opacity) {
        const shaftLength = Math.max(length * 0.9, 0.1);
        const radius = Math.max(length * 0.006, 0.08);
        const shaft = new THREE.Mesh(
            new THREE.CylinderGeometry(radius, radius, shaftLength, 10),
            new THREE.MeshBasicMaterial({ color, transparent: opacity < 1, opacity })
        );
        const half = direction.clone().multiplyScalar(shaftLength * 0.5);
        shaft.position.copy(half);
        axisAlignQuat.setFromUnitVectors(axisUnitY, direction.clone().normalize());
        shaft.quaternion.copy(axisAlignQuat);
        group.add(shaft);

        const tipHeight = Math.max(length * 0.1, 0.2);
        const tip = new THREE.Mesh(
            new THREE.ConeGeometry(radius * 2.4, tipHeight, 12),
            new THREE.MeshBasicMaterial({ color, transparent: opacity < 1, opacity })
        );
        const tipBase = direction.clone().multiplyScalar(length - tipHeight * 0.5);
        tip.position.copy(tipBase);
        tip.quaternion.copy(axisAlignQuat);
        group.add(tip);
    }

    function addAxisTicks(group, direction, tickVectors, length, step, tickCount, color) {
        const tickLen = Math.max(length * 0.035, 0.4);
        const points = [];

        for (let i = 1; i <= tickCount; i += 1) {
            const value = i * step;
            if (value > length + 0.001) {
                break;
            }

            const pos = direction.clone().multiplyScalar(value);
            const neg = direction.clone().multiplyScalar(-value);

            tickVectors.forEach((tickDir) => {
                const offset = tickDir.clone().multiplyScalar(tickLen * 0.5);
                points.push(
                    pos.x - offset.x, pos.y - offset.y, pos.z - offset.z,
                    pos.x + offset.x, pos.y + offset.y, pos.z + offset.z
                );
                points.push(
                    neg.x - offset.x, neg.y - offset.y, neg.z - offset.z,
                    neg.x + offset.x, neg.y + offset.y, neg.z + offset.z
                );
            });

            const labelPos = pos.clone().add(tickVectors[0].clone().multiplyScalar(tickLen * 1.6));
            const labelNeg = neg.clone().sub(tickVectors[0].clone().multiplyScalar(tickLen * 1.6));
            const colorCss = colorHexFromNumber(color);
            const numSpritePos = makeAxisLabelSprite(String(value), colorCss, 14);
            const numSpriteNeg = makeAxisLabelSprite(String(-value), colorCss, 14);
            if (numSpritePos) {
                numSpritePos.position.copy(labelPos);
                group.add(numSpritePos);
            }
            if (numSpriteNeg) {
                numSpriteNeg.position.copy(labelNeg);
                group.add(numSpriteNeg);
            }
        }

        if (points.length) {
            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(points, 3));
            const lines = new THREE.LineSegments(
                geometry,
                new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.85 })
            );
            group.add(lines);
        }
    }

    function buildProAxesGroup() {
        const { length, step, tickCount } = computeAxesSpec();
        const group = new THREE.Group();
        group.name = 'case-pro-axes';

        const origin = new THREE.Mesh(
            new THREE.SphereGeometry(Math.max(length * 0.012, 0.15), 12, 12),
            new THREE.MeshBasicMaterial({ color: 0xffffff })
        );
        group.add(origin);

        const axisDefs = [
            {
                dir: new THREE.Vector3(1, 0, 0),
                tickDirs: [new THREE.Vector3(0, 1, 0), new THREE.Vector3(0, 0, 1)],
                style: AXIS_STYLE.x,
            },
            {
                dir: new THREE.Vector3(0, 1, 0),
                tickDirs: [new THREE.Vector3(1, 0, 0), new THREE.Vector3(0, 0, 1)],
                style: AXIS_STYLE.y,
            },
            {
                dir: new THREE.Vector3(0, 0, 1),
                tickDirs: [new THREE.Vector3(1, 0, 0), new THREE.Vector3(0, 1, 0)],
                style: AXIS_STYLE.z,
            },
        ];

        axisDefs.forEach(({ dir, tickDirs, style }) => {
            const color = style.color;
            const colorCss = colorHexFromNumber(color);

            addAxisShaft(group, dir, length, color, 1);
            addAxisShaft(group, dir.clone().negate(), length * 0.55, color, 0.35);
            addAxisTicks(group, dir, tickDirs, length, step, tickCount, color);

            const endLabel = makeAxisLabelSprite(`${style.label} (${style.name})`, colorCss, 16);
            if (endLabel) {
                endLabel.position.copy(dir.clone().multiplyScalar(length + length * 0.08));
                group.add(endLabel);
            }

            const negLabel = makeAxisLabelSprite(style.negLabel, colorCss, 14);
            if (negLabel) {
                negLabel.position.copy(dir.clone().multiplyScalar(-length * 0.58));
                group.add(negLabel);
            }
        });

        const originLabel = makeAxisLabelSprite('0', '#334155', 14);
        if (originLabel) {
            originLabel.position.set(length * 0.06, length * 0.06, 0);
            group.add(originLabel);
        }

        return group;
    }

    function refreshAxesIfVisible() {
        if (!viewerState.showAxes) {
            return;
        }
        disposeAxesHelper();
        axesHelper = buildProAxesGroup();
        scene.add(axesHelper);
    }

    function toggleAxes() {
        viewerState.showAxes = !viewerState.showAxes;
        if (viewerState.showAxes) {
            refreshAxesIfVisible();
        } else {
            disposeAxesHelper();
        }
        setToggleActive('axes', viewerState.showAxes);
    }

    function setGrid(on) {
        viewerState.showGrid = on;
        if (on && !gridHelper) {
            const gridColors = getGridColors();
            gridHelper = new THREE.GridHelper(240, 24, gridColors.center, gridColors.grid);
            scene.add(gridHelper);
            updateGridPosition();
        } else if (!on && gridHelper) {
            scene.remove(gridHelper);
            gridHelper.geometry?.dispose();
            const mats = gridHelper.material;
            if (Array.isArray(mats)) {
                mats.forEach((m) => m.dispose());
            } else {
                mats?.dispose();
            }
            gridHelper = null;
        }
        setToggleActive('grid', on);
    }

    function toggleGrid() {
        setGrid(!viewerState.showGrid);
    }

    function setMoveMode(on) {
        viewerState.moveMode = on;

        if (on) {
            autoRotateBeforeMove = viewerState.autoRotate;
            if (viewerState.autoRotate) {
                setAutoRotate(false);
            }
            const defaultId = getDefaultMoveScanId();
            if (defaultId) {
                selectScanForMove(defaultId);
            }
        } else {
            if (autoRotateBeforeMove) {
                setAutoRotate(true);
            }
            selectedScanId = null;
            syncMoveSelectionUi();
        }

        applyControlBindings();

        if (canvasWrap) {
            canvasWrap.classList.toggle('is-move-mode', on);
        }
        setToggleActive('move-model', on);
    }

    function setAutoRotate(on) {
        viewerState.autoRotate = on;
        controls.autoRotate = on;
        controls.autoRotateSpeed = 1.4;
        setToggleActive('auto-rotate', on);
    }

    function zoomCamera(factor) {
        const offset = new THREE.Vector3().subVectors(camera.position, controls.target);
        const len = offset.length();
        if (len < 1) {
            return;
        }
        offset.multiplyScalar(factor);
        camera.position.copy(controls.target).add(offset);
        controls.update();
    }

    function cycleBackground() {
        const palette = getBgColors();
        viewerState.bgIndex = (viewerState.bgIndex + 1) % palette.length;
        syncBackground();
    }

    function syncGridTheme() {
        if (!viewerState.showGrid) {
            return;
        }

        setGrid(false);
        setGrid(true);
    }

    function applyThemeDefaultBackground() {
        viewerState.bgIndex = getDefaultBgIndex();
        syncBackground();
        syncGridTheme();
    }

    function cycleLight() {
        viewerState.lightIndex = (viewerState.lightIndex + 1) % LIGHT_LEVELS.length;
        applyLighting();
    }

    function takeScreenshot() {
        renderer.render(scene, camera);
        const link = document.createElement('a');
        link.download = 'case-3d-view-' + Date.now() + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function isViewerFullscreen() {
        return document.fullscreenElement === root;
    }

    async function toggleFullscreen() {
        if (!root) {
            return;
        }

        try {
            if (!isViewerFullscreen()) {
                await root.requestFullscreen();
                setToggleActive('fullscreen', true);
            } else {
                await document.exitFullscreen();
                setToggleActive('fullscreen', false);
            }
        } catch (err) {
            console.warn('Fullscreen not available', err);
        }

        refreshViewerLayout(true);
    }

    function handleTool(tool) {
        switch (tool) {
            case 'reset-view':
                resetView();
                break;
            case 'fit-view':
                fitCameraToModels();
                break;
            case 'zoom-in':
                zoomCamera(0.82);
                break;
            case 'zoom-out':
                zoomCamera(1.2);
                break;
            case 'view-top':
                setCameraPreset('top');
                break;
            case 'view-front':
                setCameraPreset('front');
                break;
            case 'view-side':
                setCameraPreset('side');
                break;
            case 'wireframe':
                viewerState.wireframe = !viewerState.wireframe;
                applyWireframe();
                setToggleActive('wireframe', viewerState.wireframe);
                break;
            case 'flat-shading':
                viewerState.flatShading = !viewerState.flatShading;
                applyFlatShading();
                setToggleActive('flat-shading', viewerState.flatShading);
                break;
            case 'axes':
                toggleAxes();
                break;
            case 'grid':
                toggleGrid();
                break;
            case 'toggle-pan':
                setPanEnabled(!viewerState.panEnabled);
                break;
            case 'move-model':
                setMoveMode(!viewerState.moveMode);
                break;
            case 'auto-rotate':
                setAutoRotate(!viewerState.autoRotate);
                break;
            case 'show-all':
                setAllModelsVisible(true);
                break;
            case 'hide-all':
                setAllModelsVisible(false);
                break;
            case 'brightness':
                cycleLight();
                break;
            case 'background':
                cycleBackground();
                break;
            case 'screenshot':
                takeScreenshot();
                break;
            case 'fullscreen':
                toggleFullscreen();
                break;
            default:
                break;
        }
    }

    function initToolbar() {
        if (!toolbar) {
            return;
        }

        toolbar.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-scan-tool]');
            if (!btn) {
                return;
            }
            handleTool(btn.dataset.scanTool);
        });

        document.addEventListener('fullscreenchange', () => {
            const active = isViewerFullscreen();
            root.classList.toggle('is-fullscreen', active);
            setToggleActive('fullscreen', active);
            syncBackground();
            refreshViewerLayout(true);
        });

        document.body.addEventListener('lineup-color-mode-change', applyThemeDefaultBackground);
    }

    function getVisibleMeshEntries() {
        return Array.from(meshesById.values()).filter((entry) => entry.wrapper.visible);
    }

    function syncLegendVisibility(scanId, isVisible) {
        root.querySelectorAll('.case-scan-legend__item[data-scan-id]').forEach((item) => {
            if (item.getAttribute('data-scan-id') === scanId) {
                item.classList.toggle('is-off', !isVisible);
            }
        });
    }

    function syncFileCardVisibility(scanId, isVisible) {
        const card = root.querySelector('.case-scan-file-card[data-scan-id="' + scanId + '"]');
        if (card) {
            card.classList.toggle('is-hidden-in-viewer', !isVisible);
        }
    }

    function fitCameraToModels() {
        const visible = getVisibleMeshEntries();
        if (!visible.length) {
            return;
        }

        modelGroup.updateMatrixWorld(true);

        const box = new THREE.Box3();
        visible.forEach((entry) => {
            box.expandByObject(entry.wrapper);
        });

        if (box.isEmpty()) {
            return;
        }

        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z, 1);
        const vFovRad = (camera.fov * Math.PI) / 180;
        const halfVFov = Math.tan(vFovRad / 2);

        let distance = maxDim / (2 * halfVFov);

        if (camera.aspect > 0) {
            const halfHFov = halfVFov * camera.aspect;
            distance = Math.max(
                distance,
                (size.y * 0.5) / halfVFov,
                (size.x * 0.5) / halfHFov
            );
        }

        if (!Number.isFinite(distance) || distance < 1) {
            distance = maxDim * 2;
        }

        distance *= CAMERA_DISTANCE_FACTOR;

        controls.target.copy(center);
        camera.position.set(center.x, center.y + maxDim * 0.12, center.z + distance);
        controls.update();
    }

    function centerModelGroup() {
        const visible = getVisibleMeshEntries();
        if (!visible.length) {
            return;
        }

        const box = new THREE.Box3();
        visible.forEach((entry) => {
            box.expandByObject(entry.wrapper);
        });

        const center = box.getCenter(new THREE.Vector3());
        modelGroup.position.set(-center.x, -center.y, -center.z);
    }

    function refreshViewerLayout(refitCamera) {
        afterLayout(() => {
            if (resize()) {
                if (refitCamera) {
                    fitCameraToModels();
                }
            }
        });
    }

    function centerObjectInWrapper(object) {
        const box = new THREE.Box3().setFromObject(object);
        if (box.isEmpty()) {
            return;
        }

        const center = box.getCenter(new THREE.Vector3());
        object.position.sub(center);
    }

    function resetWrapperTransform(entry) {
        entry.wrapper.position.set(0, 0, 0);
        entry.wrapper.rotation.set(0, 0, 0);
    }

    /** Keep scanner/file coordinates — do not re-center geometry. */
    function resetArchToFileState(entry) {
        resetWrapperTransform(entry);
        entry.object.rotation.set(0, 0, 0);
        entry.object.position.set(0, 0, 0);
    }

    /** Min score to trust pre-registered upper/lower pairs (same as Trimesh / scanner export). */
    const REGISTERED_BITE_MIN_SCORE = 0;

    function getBoxOverlapOnAxes(boxA, boxB, axis1, axis2) {
        const overlap1 = Math.max(
            0,
            Math.min(boxA.max[axis1], boxB.max[axis1]) - Math.max(boxA.min[axis1], boxB.min[axis1])
        );
        const overlap2 = Math.max(
            0,
            Math.min(boxA.max[axis2], boxB.max[axis2]) - Math.max(boxA.min[axis2], boxB.min[axis2])
        );
        const overlapArea = overlap1 * overlap2;
        const areaA = Math.max(
            (boxA.max[axis1] - boxA.min[axis1]) * (boxA.max[axis2] - boxA.min[axis2]),
            0.001
        );
        const areaB = Math.max(
            (boxB.max[axis1] - boxB.min[axis1]) * (boxB.max[axis2] - boxB.min[axis2]),
            0.001
        );
        const unionArea = areaA + areaB - overlapArea;

        return unionArea > 0 ? overlapArea / unionArea : 0;
    }

    /** Score bite as exported — upper/lower already in shared scanner coordinates. */
    function measureRegisteredBite(upper, lower) {
        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);

        const lb = new THREE.Box3().setFromObject(lower.wrapper);
        const ub = new THREE.Box3().setFromObject(upper.wrapper);
        const axisSets = [
            { up: 'y', h1: 'x', h2: 'z' },
            { up: 'z', h1: 'x', h2: 'y' },
            { up: 'x', h1: 'y', h2: 'z' },
        ];

        let best = { score: -Infinity, upAxis: 'y', gap: 0, upperAbove: true, archSpan: 1, overlap: 0 };

        axisSets.forEach(({ up, h1, h2 }) => {
            const lowerMin = lb.min[up];
            const lowerMax = lb.max[up];
            const upperMin = ub.min[up];
            const upperMax = ub.max[up];
            const lowerCenter = (lowerMin + lowerMax) * 0.5;
            const upperCenter = (upperMin + upperMax) * 0.5;
            const lowerSpan = lowerMax - lowerMin;
            const upperSpan = upperMax - upperMin;
            const archSpan = Math.max(lowerSpan, upperSpan, 0.001);
            const overlap = getBoxOverlapOnAxes(lb, ub, h1, h2);

            const variants = [
                { upperAbove: true, gap: upperMin - lowerMax },
                { upperAbove: false, gap: lowerMin - upperMax },
            ];

            variants.forEach(({ upperAbove, gap }) => {
                if (upperAbove && upperCenter <= lowerCenter) {
                    return;
                }
                if (!upperAbove && upperCenter >= lowerCenter) {
                    return;
                }

                let score = overlap * 24;
                if (gap >= -archSpan * 0.06 && gap <= archSpan * 0.1) {
                    score += 16;
                } else if (Math.abs(gap) <= archSpan * 0.22) {
                    score += 8 - Math.abs(gap) * 0.35;
                } else {
                    score -= Math.abs(gap) * 1.2;
                }

                if (overlap > 0.42) {
                    score += 10;
                }

                if (score > best.score) {
                    best = { score, upAxis: up, gap, upperAbove, archSpan, h1, h2, overlap };
                }
            });
        });

        return best;
    }

    function closeRegisteredBiteGap(upper, lower, analysis) {
        if (!analysis || !analysis.upAxis) {
            return;
        }

        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);

        const lb = new THREE.Box3().setFromObject(lower.wrapper);
        const ub = new THREE.Box3().setFromObject(upper.wrapper);
        const up = analysis.upAxis;
        const maxPen = (analysis.archSpan || 1) * 0.015;

        if (analysis.upperAbove !== false) {
            const gap = ub.min[up] - lb.max[up];
            if (gap > 0.005) {
                upper.wrapper.position[up] -= gap;
            } else if (gap < -maxPen) {
                upper.wrapper.position[up] -= gap + maxPen;
            }
        } else {
            const gap = lb.min[up] - ub.max[up];
            if (gap > 0.005) {
                lower.wrapper.position[up] -= gap;
            } else if (gap < -maxPen) {
                lower.wrapper.position[up] -= gap + maxPen;
            }
        }
    }

    function forceCloseRegisteredGap(upper, lower) {
        closeRegisteredBiteGap(upper, lower, measureRegisteredBite(upper, lower));

        ['y', 'z', 'x'].forEach((axis) => {
            lower.wrapper.updateMatrixWorld(true);
            upper.wrapper.updateMatrixWorld(true);

            const lb = new THREE.Box3().setFromObject(lower.wrapper);
            const ub = new THREE.Box3().setFromObject(upper.wrapper);
            const lowerMid = (lb.min[axis] + lb.max[axis]) * 0.5;
            const upperMid = (ub.min[axis] + ub.max[axis]) * 0.5;

            if (upperMid <= lowerMid) {
                return;
            }

            const gap = ub.min[axis] - lb.max[axis];
            if (gap > 0.005) {
                upper.wrapper.position[axis] -= gap;
            }
        });
    }

    function ensureBiteGroupPair(upper, lower) {
        if (!biteGroup.parent) {
            modelGroup.add(biteGroup);
        }

        if (upper.wrapper.parent !== biteGroup) {
            biteGroup.attach(upper.wrapper);
        }

        if (lower.wrapper.parent !== biteGroup) {
            biteGroup.attach(lower.wrapper);
        }
    }

    function detachFromBiteGroup(entry) {
        if (entry.wrapper.parent === biteGroup) {
            modelGroup.attach(entry.wrapper);
        }
    }

    function resetBiteGroupOrientation() {
        biteGroup.rotation.set(0, 0, 0);
        biteGroup.position.set(0, 0, 0);
    }

    /** Move bite content so the pair pivot is at biteGroup origin — keeps jaw registration. */
    function recenterBiteGroupLocally() {
        if (!biteGroup.children.length) {
            return;
        }

        const box = new THREE.Box3();
        biteGroup.children.forEach((child) => {
            box.expandByObject(child);
        });

        if (box.isEmpty()) {
            return;
        }

        const center = box.getCenter(new THREE.Vector3());
        biteGroup.children.forEach((child) => {
            child.position.sub(center);
        });
    }

    function orientBiteGroupForFrontView(upper, lower) {
        const xRotations = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
        const yRotations = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
        let best = { rotX: 0, rotY: 0, score: -Infinity };

        yRotations.forEach((rotY) => {
            xRotations.forEach((rotX) => {
                biteGroup.rotation.set(rotX, rotY, 0);
                biteGroup.updateMatrixWorld(true);

                const reg = measureRegisteredBite(upper, lower);
                const front = getClinicalFrontScore(upper, lower);
                const horizontal = getOcclusalHorizontalScore(upper, lower);
                const score = reg.score * 8 + front * 2 + horizontal * 5;

                if (score > best.score) {
                    best = { rotX, rotY, score };
                }
            });
        });

        biteGroup.rotation.set(best.rotX, best.rotY, 0);
        biteGroup.updateMatrixWorld(true);
    }

    function refineBiteGroupFrontYaw(upper, lower) {
        const baseRotX = biteGroup.rotation.x;
        const baseRotY = biteGroup.rotation.y;

        const tryYaw = (rotY) => {
            biteGroup.rotation.set(baseRotX, rotY, 0);
            biteGroup.updateMatrixWorld(true);
            return getClinicalFrontScore(upper, lower);
        };

        const score0 = tryYaw(baseRotY);
        const score180 = tryYaw(baseRotY + Math.PI);
        biteGroup.rotation.set(baseRotX, score180 > score0 ? baseRotY + Math.PI : baseRotY, 0);
        biteGroup.updateMatrixWorld(true);
    }

    /** Trimesh-style: preserve scanner registration, rotate the pair as one group. */
    function stackRegisteredBite(upper, lower) {
        resetArchToFileState(upper);
        resetArchToFileState(lower);
        resetBiteGroupOrientation();
        ensureBiteGroupPair(upper, lower);

        forceCloseRegisteredGap(upper, lower);
        recenterBiteGroupLocally();

        orientBiteGroupForFrontView(upper, lower);
        forceCloseRegisteredGap(upper, lower);
        recenterBiteGroupLocally();

        refineBiteGroupFrontYaw(upper, lower);
        forceCloseRegisteredGap(upper, lower);
    }

    function stackAutoOrientedBite(upper, lower) {
        resetBiteGroupOrientation();
        detachFromBiteGroup(upper);
        detachFromBiteGroup(lower);
        resetArchToFileState(upper);
        resetArchToFileState(lower);
        centerObjectInWrapper(lower.object);
        centerObjectInWrapper(upper.object);

        const best = findBestBiteOrientation(upper, lower);
        ensureFrontSmileOrientation(upper, lower, best);
    }

    /** Convert common scanner axes (Z-up / X-up) to Y-up. */
    function normalizeToYUp(object, wrapper) {
        object.rotation.set(0, 0, 0);
        centerObjectInWrapper(object);

        let box = new THREE.Box3().setFromObject(wrapper);
        let size = box.getSize(new THREE.Vector3());

        if (size.z > size.y * 1.05 && size.z >= size.x * 0.65) {
            object.rotation.x = -Math.PI / 2;
            centerObjectInWrapper(object);
            box.setFromObject(wrapper);
            size = box.getSize(new THREE.Vector3());
        }

        if (size.x > size.y * 1.05 && size.x >= size.z * 0.65) {
            object.rotation.z = Math.PI / 2;
            centerObjectInWrapper(object);
        }
    }

    function collectWrapperPoints(wrapper, step = 6) {
        const points = [];
        wrapper.updateMatrixWorld(true);
        wrapper.traverse((child) => {
            if (!child.isMesh || !child.geometry?.attributes?.position) {
                return;
            }

            const positions = child.geometry.attributes.position;
            const stride = positions.count > 100000 ? Math.max(step, 10) : step;

            for (let i = 0; i < positions.count; i += stride) {
                occlusalSampleVec.fromBufferAttribute(positions, i);
                occlusalSampleVec.applyMatrix4(child.matrixWorld);
                points.push(occlusalSampleVec.clone());
            }
        });

        return points;
    }

    function computeCoordinateVariances(wrapper, step = 6) {
        const points = collectWrapperPoints(wrapper, step);
        if (points.length < 3) {
            return { x: 1, y: 1, z: 1 };
        }

        const mean = new THREE.Vector3();
        points.forEach((point) => mean.add(point));
        mean.divideScalar(points.length);

        let vx = 0;
        let vy = 0;
        let vz = 0;
        points.forEach((point) => {
            vx += (point.x - mean.x) ** 2;
            vy += (point.y - mean.y) ** 2;
            vz += (point.z - mean.z) ** 2;
        });

        const n = points.length;
        return { x: vx / n, y: vy / n, z: vz / n };
    }

    /**
     * Lay the arch flat: smallest spread axis becomes world Y (occlusal plane = XZ).
     * Handles scans exported "standing" with occlusal facing the camera.
     */
    function alignArchThinnestAxisToY(object, wrapper, arch) {
        object.rotation.set(0, 0, 0);
        centerObjectInWrapper(object);
        wrapper.updateMatrixWorld(true);

        const pickBestTilt = (candidates) => {
            let bestRx = 0;
            let bestScore = -Infinity;

            candidates.forEach((rx) => {
                object.rotation.set(rx, 0, 0);
                centerObjectInWrapper(object);
                wrapper.updateMatrixWorld(true);

                const vars = computeCoordinateVariances(wrapper);
                const flatness = Math.min(vars.x, vars.y, vars.z) / (Math.max(vars.x, vars.y, vars.z) + 0.001);
                const facing = getArchFacingScore(wrapper, arch);
                const score = facing * 3 + flatness;

                if (score > bestScore) {
                    bestScore = score;
                    bestRx = rx;
                }
            });

            object.rotation.set(bestRx, 0, 0);
            centerObjectInWrapper(object);
        };

        let vars = computeCoordinateVariances(wrapper);
        const order = [
            ['x', vars.x],
            ['y', vars.y],
            ['z', vars.z],
        ].sort((a, b) => a[1] - b[1]);
        const thinnest = order[0][0];

        if (thinnest === 'z') {
            pickBestTilt([-Math.PI / 2, Math.PI / 2]);
        } else if (thinnest === 'x') {
            object.rotation.z = Math.PI / 2;
            centerObjectInWrapper(object);
            wrapper.updateMatrixWorld(true);
            vars = computeCoordinateVariances(wrapper);
            if (vars.z < vars.y && vars.z < vars.x) {
                pickBestTilt([-Math.PI / 2, Math.PI / 2]);
            }
        }

        centerObjectInWrapper(object);
    }

    function resolveArchOcclusalFlip(object, wrapper, arch) {
        wrapper.updateMatrixWorld(true);
        const baseScore = getArchFacingScore(wrapper, arch);

        object.rotation.x += Math.PI;
        centerObjectInWrapper(object);
        wrapper.updateMatrixWorld(true);
        const flippedScore = getArchFacingScore(wrapper, arch);

        if (flippedScore <= baseScore) {
            object.rotation.x -= Math.PI;
            centerObjectInWrapper(object);
        }
    }

    function prepareArchForBite(object, wrapper, arch) {
        normalizeToYUp(object, wrapper);
        alignArchThinnestAxisToY(object, wrapper, arch);
        resolveArchOcclusalFlip(object, wrapper, arch);
    }

    const occlusalSampleVec = new THREE.Vector3();
    const occlusalNormalVec = new THREE.Vector3();

    function ensureMeshNormals(object) {
        object.traverse((child) => {
            if (!child.isMesh || !child.geometry) {
                return;
            }
            if (!child.geometry.attributes.normal) {
                child.geometry.computeVertexNormals();
            }
        });
    }

    /** Weighted mean Y of occlusal-facing vertices (lower = up, upper = down). */
    function getOcclusalPlaneY(wrapper, arch) {
        let sumY = 0;
        let sumWeight = 0;

        wrapper.updateMatrixWorld(true);
        wrapper.traverse((child) => {
            if (!child.isMesh || !child.geometry?.attributes?.position) {
                return;
            }

            const positions = child.geometry.attributes.position;
            const normals = child.geometry.attributes.normal;
            const threshold = 0.25;
            const step = positions.count > 120000 ? 3 : 1;

            for (let i = 0; i < positions.count; i += step) {
                occlusalSampleVec.fromBufferAttribute(positions, i);
                occlusalSampleVec.applyMatrix4(child.matrixWorld);

                let weight = 0;
                if (normals) {
                    occlusalNormalVec.fromBufferAttribute(normals, i);
                    occlusalNormalVec.transformDirection(child.matrixWorld);
                    if (arch === 'lower' && occlusalNormalVec.y > threshold) {
                        weight = occlusalNormalVec.y;
                    } else if (arch === 'upper' && occlusalNormalVec.y < -threshold) {
                        weight = -occlusalNormalVec.y;
                    }
                } else if (arch === 'lower' && occlusalSampleVec.y > 0) {
                    weight = 1;
                } else if (arch === 'upper' && occlusalSampleVec.y < 0) {
                    weight = 1;
                }

                if (weight > 0) {
                    sumY += occlusalSampleVec.y * weight;
                    sumWeight += weight;
                }
            }
        });

        if (sumWeight > 0) {
            return sumY / sumWeight;
        }

        return getArchOcclusalYFallback(wrapper, arch);
    }

    /** How confidently the arch faces the correct way (lower up, upper down). */
    function getArchFacingScore(wrapper, arch) {
        let correct = 0;
        let samples = 0;
        const threshold = 0.35;

        wrapper.updateMatrixWorld(true);
        wrapper.traverse((child) => {
            if (!child.isMesh || !child.geometry?.attributes?.position) {
                return;
            }

            const positions = child.geometry.attributes.position;
            const normals = child.geometry.attributes.normal;
            if (!normals) {
                return;
            }

            const step = positions.count > 80000 ? 4 : 2;
            for (let i = 0; i < positions.count; i += step) {
                occlusalNormalVec.fromBufferAttribute(normals, i);
                occlusalNormalVec.transformDirection(child.matrixWorld);
                samples += 1;
                if (arch === 'lower' && occlusalNormalVec.y > threshold) {
                    correct += occlusalNormalVec.y;
                } else if (arch === 'upper' && occlusalNormalVec.y < -threshold) {
                    correct += -occlusalNormalVec.y;
                }
            }
        });

        return samples > 0 ? correct / samples : 0;
    }

    function getBoxXzOverlapRatio(boxA, boxB) {
        const overlapX = Math.max(0, Math.min(boxA.max.x, boxB.max.x) - Math.max(boxA.min.x, boxB.min.x));
        const overlapZ = Math.max(0, Math.min(boxA.max.z, boxB.max.z) - Math.max(boxA.min.z, boxB.min.z));
        const overlapArea = overlapX * overlapZ;
        const areaA = Math.max((boxA.max.x - boxA.min.x) * (boxA.max.z - boxA.min.z), 0.001);
        const areaB = Math.max((boxB.max.x - boxB.min.x) * (boxB.max.z - boxB.min.z), 0.001);
        const unionArea = areaA + areaB - overlapArea;

        return unionArea > 0 ? overlapArea / unionArea : 0;
    }

    /** Occlusal normals should lie in the horizontal (XZ) plane — high |Y| on bite surfaces. */
    function getOcclusalHorizontalScore(upper, lower) {
        let sum = 0;
        let samples = 0;
        const wrappers = [
            { wrapper: lower.wrapper, arch: 'lower' },
            { wrapper: upper.wrapper, arch: 'upper' },
        ];

        wrappers.forEach(({ wrapper, arch }) => {
            wrapper.updateMatrixWorld(true);
            wrapper.traverse((child) => {
                if (!child.isMesh || !child.geometry?.attributes?.position) {
                    return;
                }

                const positions = child.geometry.attributes.position;
                const normals = child.geometry.attributes.normal;
                if (!normals) {
                    return;
                }

                const threshold = 0.25;
                const step = positions.count > 80000 ? 4 : 2;

                for (let i = 0; i < positions.count; i += step) {
                    occlusalNormalVec.fromBufferAttribute(normals, i);
                    occlusalNormalVec.transformDirection(child.matrixWorld);

                    let isOcclusal = false;
                    if (arch === 'lower' && occlusalNormalVec.y > threshold) {
                        isOcclusal = true;
                    } else if (arch === 'upper' && occlusalNormalVec.y < -threshold) {
                        isOcclusal = true;
                    }

                    if (isOcclusal) {
                        sum += Math.abs(occlusalNormalVec.y);
                        samples += 1;
                    }
                }
            });
        });

        return samples > 0 ? sum / samples : 0;
    }

    /**
     * Score how well outer (labial/buccal) tooth faces point toward the camera axis.
     * dirZ: +1 = camera at +Z, −1 = camera at −Z.
     */
    function getSmileFacingScore(upper, lower, dirZ) {
        let smile = 0;
        let occlusalToCamera = 0;
        let samples = 0;
        const wrappers = [lower.wrapper, upper.wrapper];

        wrappers.forEach((wrapper) => {
            wrapper.updateMatrixWorld(true);
            wrapper.traverse((child) => {
                if (!child.isMesh || !child.geometry?.attributes?.position) {
                    return;
                }

                const positions = child.geometry.attributes.position;
                const normals = child.geometry.attributes.normal;
                if (!normals) {
                    return;
                }

                const step = positions.count > 80000 ? 4 : 2;

                for (let i = 0; i < positions.count; i += step) {
                    occlusalNormalVec.fromBufferAttribute(normals, i);
                    occlusalNormalVec.transformDirection(child.matrixWorld);
                    samples += 1;

                    const towardCamera = occlusalNormalVec.z * dirZ;
                    const absY = Math.abs(occlusalNormalVec.y);

                    if (towardCamera > 0.28 && absY < 0.62) {
                        smile += towardCamera;
                    }

                    if (absY > 0.52 && towardCamera > 0.42) {
                        occlusalToCamera += towardCamera;
                    }
                }
            });
        });

        if (samples === 0) {
            return 0;
        }

        return (smile / samples) - (occlusalToCamera / samples) * 5;
    }

    /** Combined score for a clinical front (smile) presentation — not occlusal-toward-camera. */
    function getClinicalFrontScore(upper, lower) {
        const horizontal = getOcclusalHorizontalScore(upper, lower);
        const smilePlusZ = getSmileFacingScore(upper, lower, 1);
        const smileMinusZ = getSmileFacingScore(upper, lower, -1);
        const smile = Math.max(smilePlusZ, smileMinusZ);

        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);
        const combined = new THREE.Box3()
            .setFromObject(lower.wrapper)
            .union(new THREE.Box3().setFromObject(upper.wrapper));
        const combinedSize = combined.getSize(new THREE.Vector3());
        const widthDepth = combinedSize.x / Math.max(combinedSize.z, 0.001);

        return horizontal * 5 + smile * 14 + Math.min(widthDepth, 4) * 0.75;
    }

    /** Which world Z direction the anterior (smile) should face for the front camera. */
    function getFrontCameraDirection(upper, lower) {
        const plus = getSmileFacingScore(upper, lower, 1);
        const minus = getSmileFacingScore(upper, lower, -1);
        return plus >= minus ? 1 : -1;
    }

    function getBiteBounds(upper, lower) {
        const box = new THREE.Box3().setFromObject(lower.wrapper);
        box.union(new THREE.Box3().setFromObject(upper.wrapper));
        return box;
    }

    /** Aim at incisors — slightly below mid-height, toward the anterior of the arch. */
    function getFrontFocusPoint(upper, lower, dirZ) {
        const box = getBiteBounds(upper, lower);
        const center = box.getCenter(new THREE.Vector3());
        const size = box.getSize(new THREE.Vector3());
        const anteriorZ = dirZ > 0
            ? box.max.z - size.z * 0.2
            : box.min.z + size.z * 0.2;

        return new THREE.Vector3(center.x, center.y - size.y * 0.06, anteriorZ);
    }

    function computeFrontCameraDistance(focus, size) {
        const maxDim = Math.max(size.x, size.y, size.z, 1);
        const vFovRad = (camera.fov * Math.PI) / 180;
        const halfVFov = Math.tan(vFovRad / 2);

        let distance = maxDim / (2 * halfVFov);

        if (camera.aspect > 0) {
            const halfHFov = halfVFov * camera.aspect;
            distance = Math.max(
                distance,
                (size.y * 0.55) / halfVFov,
                (size.x * 0.52) / halfHFov
            );
        }

        if (!Number.isFinite(distance) || distance < 1) {
            distance = maxDim * 2;
        }

        return distance * CAMERA_DISTANCE_FACTOR;
    }

    function applyFrontBiteCameraView() {
        const upper = meshesById.get('upper');
        const lower = meshesById.get('lower');
        if (!upper || !lower || !upper.wrapper.visible || !lower.wrapper.visible) {
            fitCameraToModels();
            return;
        }

        modelGroup.updateMatrixWorld(true);

        const dirZ = getFrontCameraDirection(upper, lower);
        const box = getBiteBounds(upper, lower);
        const size = box.getSize(new THREE.Vector3());
        const focus = getFrontFocusPoint(upper, lower, dirZ);
        const distance = computeFrontCameraDistance(focus, size);

        controls.target.copy(focus);
        camera.position.set(focus.x, focus.y, focus.z + distance * dirZ);
        camera.up.set(0, 1, 0);
        controls.update();
    }

    function getArchOcclusalYFallback(wrapper, arch) {
        const ys = [];

        wrapper.updateMatrixWorld(true);
        wrapper.traverse((child) => {
            if (!child.isMesh || !child.geometry?.attributes?.position) {
                return;
            }

            const positions = child.geometry.attributes.position;
            for (let i = 0; i < positions.count; i += 1) {
                occlusalSampleVec.fromBufferAttribute(positions, i);
                occlusalSampleVec.applyMatrix4(child.matrixWorld);
                ys.push(occlusalSampleVec.y);
            }
        });

        if (!ys.length) {
            const box = new THREE.Box3().setFromObject(wrapper);
            return arch === 'lower' ? box.max.y : box.min.y;
        }

        ys.sort((a, b) => a - b);
        const index = arch === 'lower'
            ? Math.floor(ys.length * 0.9)
            : Math.floor(ys.length * 0.1);

        return ys[Math.min(Math.max(index, 0), ys.length - 1)];
    }

    function alignArchCentersXZ(upper, lower) {
        const lowerBox = new THREE.Box3().setFromObject(lower.wrapper);
        const upperBox = new THREE.Box3().setFromObject(upper.wrapper);
        const midX = ((lowerBox.min.x + lowerBox.max.x) + (upperBox.min.x + upperBox.max.x)) * 0.25;
        const midZ = ((lowerBox.min.z + lowerBox.max.z) + (upperBox.min.z + upperBox.max.z)) * 0.25;

        lower.wrapper.position.x -= midX;
        lower.wrapper.position.z -= midZ;
        upper.wrapper.position.x -= midX;
        upper.wrapper.position.z -= midZ;
    }

    /**
     * Stack arches on a shared occlusal plane (y = 0), then settle to light contact.
     * Lower occlusal faces up; upper occlusal faces down — like clinical QA bite viewers.
     */
    function alignBiteOcclusal(upper, lower, applyClosedBite = true) {
        lower.wrapper.position.set(0, 0, 0);
        upper.wrapper.position.set(0, 0, 0);

        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);

        const lowerOcc = getOcclusalPlaneY(lower.wrapper, 'lower');
        const upperOcc = getOcclusalPlaneY(upper.wrapper, 'upper');

        lower.wrapper.position.y = -lowerOcc;
        upper.wrapper.position.y = -upperOcc;

        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);
        alignArchCentersXZ(upper, lower);

        if (!applyClosedBite) {
            return;
        }

        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);

        const lowerBox = new THREE.Box3().setFromObject(lower.wrapper);
        const upperBox = new THREE.Box3().setFromObject(upper.wrapper);
        const gap = upperBox.min.y - lowerBox.max.y;
        const archHeight = Math.min(
            lowerBox.getSize(new THREE.Vector3()).y,
            upperBox.getSize(new THREE.Vector3()).y,
            40
        );
        const maxPenetration = archHeight * 0.012;
        const targetGap = 0;

        if (gap > targetGap + 0.05) {
            upper.wrapper.position.y -= gap - targetGap;
        } else if (gap < -maxPenetration) {
            upper.wrapper.position.y -= gap + maxPenetration;
        }
    }

    function scoreBiteAlignment(upper, lower) {
        alignBiteOcclusal(upper, lower, false);

        lower.wrapper.updateMatrixWorld(true);
        upper.wrapper.updateMatrixWorld(true);

        const lowerBox = new THREE.Box3().setFromObject(lower.wrapper);
        const upperBox = new THREE.Box3().setFromObject(upper.wrapper);
        const lowerSize = lowerBox.getSize(new THREE.Vector3());
        const upperSize = upperBox.getSize(new THREE.Vector3());
        const lowerCenterY = (lowerBox.min.y + lowerBox.max.y) * 0.5;
        const upperCenterY = (upperBox.min.y + upperBox.max.y) * 0.5;

        if (upperCenterY <= lowerCenterY) {
            return -1000;
        }

        const horizontal = getOcclusalHorizontalScore(upper, lower);
        if (horizontal < 0.32) {
            return -900 - (0.32 - horizontal) * 120;
        }

        const gap = upperBox.min.y - lowerBox.max.y;
        const archHeight = Math.max(Math.min(lowerSize.y, upperSize.y), 1);
        const xzOverlap = getBoxXzOverlapRatio(lowerBox, upperBox);
        const lowerFacing = getArchFacingScore(lower.wrapper, 'lower');
        const upperFacing = getArchFacingScore(upper.wrapper, 'upper');
        const combined = lowerBox.clone().union(upperBox);
        const combinedSize = combined.getSize(new THREE.Vector3());
        const frontalScore = combinedSize.x / Math.max(combinedSize.z, 0.001);
        const stackRatio = combinedSize.y / (lowerSize.y + upperSize.y + 0.001);

        let score = 0;
        score += horizontal * 10;
        score += xzOverlap * 8;
        score += lowerFacing * 3 + upperFacing * 3;
        score += frontalScore * 0.6;

        if (stackRatio > 0.42 && stackRatio < 0.92) {
            score += 4;
        } else {
            score -= Math.abs(stackRatio - 0.72) * 6;
        }

        if (gap >= -archHeight * 0.02 && gap <= archHeight * 0.06) {
            score += 5;
        } else if (gap > archHeight * 0.06) {
            score -= gap * 4;
        } else {
            score -= Math.abs(gap) * 12;
        }

        score += getClinicalFrontScore(upper, lower) * 2.5;

        return score;
    }

    function applyArchRotation(object, wrapper, rotX, rotY) {
        object.rotation.set(rotX, rotY, 0);
        centerObjectInWrapper(object);
    }

    /** Search orientations and pick a natural stacked QA bite (upper above lower, occlusal contact). */
    function findBestBiteOrientation(upper, lower) {
        prepareArchForBite(lower.object, lower.wrapper, 'lower');
        prepareArchForBite(upper.object, upper.wrapper, 'upper');

        const yRotations = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
        const xRotations = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
        let bestScore = -Infinity;
        let best = { rotY: 0, lowerRotX: 0, upperRotX: Math.PI };

        yRotations.forEach((rotY) => {
            xRotations.forEach((lowerRotX) => {
                xRotations.forEach((upperRotX) => {
                    applyArchRotation(lower.object, lower.wrapper, lowerRotX, rotY);
                    applyArchRotation(upper.object, upper.wrapper, upperRotX, rotY);

                    const score = scoreBiteAlignment(upper, lower);
                    if (score > bestScore) {
                        bestScore = score;
                        best = { rotY, lowerRotX, upperRotX };
                    }
                });
            });
        });

        if (bestScore <= -500) {
            best = { rotY: 0, lowerRotX: 0, upperRotX: Math.PI };
        }

        return best;
    }

    /** Pick 0° or 180° yaw so labial surfaces face the front camera, not occlusal. */
    function ensureFrontSmileOrientation(upper, lower, best) {
        const tryOrientation = (rotY) => {
            applyArchRotation(lower.object, lower.wrapper, best.lowerRotX, rotY);
            applyArchRotation(upper.object, upper.wrapper, best.upperRotX, rotY);
            alignBiteOcclusal(upper, lower, true);
            return getClinicalFrontScore(upper, lower);
        };

        const score0 = tryOrientation(best.rotY);
        const score180 = tryOrientation(best.rotY + Math.PI);
        const rotY = score180 > score0 ? best.rotY + Math.PI : best.rotY;

        applyArchRotation(lower.object, lower.wrapper, best.lowerRotX, rotY);
        applyArchRotation(upper.object, upper.wrapper, best.upperRotX, rotY);
        alignBiteOcclusal(upper, lower, true);

        return rotY;
    }

    function findBestFacingRotation(object, wrapper) {
        prepareArchForBite(object, wrapper, 'lower');

        const xRotations = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
        const yRotations = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
        let bestRotation = 0;
        let bestRotX = 0;
        let bestScore = -Infinity;

        xRotations.forEach((rotX) => {
            yRotations.forEach((yRotation) => {
                object.rotation.set(rotX, yRotation, 0);
                centerObjectInWrapper(object);

                const size = new THREE.Box3().setFromObject(wrapper).getSize(new THREE.Vector3());
                const score = size.x / Math.max(size.z, 0.001);

                if (score > bestScore) {
                    bestScore = score;
                    bestRotation = yRotation;
                    bestRotX = rotX;
                }
            });
        });

        object.rotation.set(bestRotX, bestRotation, 0);
        centerObjectInWrapper(object);

        return bestRotation;
    }

    function stackUpperLowerMeshes(upper, lower) {
        resetArchToFileState(upper);
        resetArchToFileState(lower);

        ensureMeshNormals(lower.object);
        ensureMeshNormals(upper.object);

        const registered = measureRegisteredBite(upper, lower);

        if (registered.score >= REGISTERED_BITE_MIN_SCORE || (registered.overlap ?? 0) > 0.15) {
            stackRegisteredBite(upper, lower);
        } else {
            stackAutoOrientedBite(upper, lower);
        }

        const lowerBox = new THREE.Box3().setFromObject(lower.wrapper);
        const upperBox = new THREE.Box3().setFromObject(upper.wrapper);
        lower.size = lowerBox.getSize(new THREE.Vector3());
        upper.size = upperBox.getSize(new THREE.Vector3());
    }

    function updateGridPosition() {
        if (!gridHelper) {
            return;
        }

        const box = new THREE.Box3();
        getVisibleMeshEntries().forEach((entry) => {
            box.expandByObject(entry.wrapper);
        });

        if (box.isEmpty()) {
            gridHelper.position.y = -0.01;
            return;
        }

        gridHelper.position.y = box.min.y - Math.max(box.getSize(new THREE.Vector3()).y * 0.02, 0.5);
    }

    function fitFrontBiteCamera() {
        applyFrontBiteCameraView();
    }

    function applyInitialCameraView() {
        const upper = meshesById.get('upper');
        const lower = meshesById.get('lower');
        const hasBitePair = upper
            && lower
            && upper.wrapper.visible
            && lower.wrapper.visible;

        if (hasBitePair) {
            fitFrontBiteCamera();
            return;
        }

        fitCameraToModels();
    }

    function layoutMeshes() {
        const entries = Array.from(meshesById.values());
        if (!entries.length) {
            return;
        }

        if (entries.length < 2) {
            entries.forEach((entry) => {
                entry.wrapper.position.set(0, 0, 0);
                ensureMeshNormals(entry.object);
                centerObjectInWrapper(entry.object);
                findBestFacingRotation(entry.object, entry.wrapper);
            });
        } else {
            entries.forEach((entry) => {
                const box = new THREE.Box3().setFromObject(entry.wrapper);
                entry.size = box.getSize(new THREE.Vector3());
            });

            const upper = meshesById.get('upper');
            const lower = meshesById.get('lower');

            if (upper && lower) {
                stackUpperLowerMeshes(upper, lower);
            } else {
                let offset = 0;
                entries.forEach((entry) => {
                    const step = Math.max(entry.size.y, 20);
                    entry.wrapper.position.set(0, offset, 0);
                    offset += step * 1.1;
                });
            }
        }

        modelGroup.position.set(0, 0, 0);
        centerModelGroup();
        updateGridPosition();
        applyInitialCameraView();
        captureLayoutSnapshot();
        refreshAxesIfVisible();
    }

    function loadObjWithMaterials(url, onLoaded, onError) {
        const basePath = url.includes('/') ? url.substring(0, url.lastIndexOf('/') + 1) : '';
        const mtlUrl = url.replace(/\.obj(\?.*)?$/i, '.mtl$1');
        const objLoader = new OBJLoader();
        const mtlLoader = new MTLLoader();

        if (basePath) {
            mtlLoader.setResourcePath(basePath);
        }

        mtlLoader.load(
            mtlUrl,
            (materials) => {
                materials.preload();
                objLoader.setMaterials(materials);
                objLoader.load(url, onLoaded, undefined, onError);
            },
            undefined,
            () => objLoader.load(url, onLoaded, undefined, onError)
        );
    }

    function loadMesh(scan) {
        return new Promise((resolve, reject) => {
            const ext = (scan.ext || 'STL').toLowerCase();
            const url = scan.view_url;
            const scanId = String(scan.id || '').trim();
            if (!scanId) {
                reject(new Error('Missing scan id'));
                return;
            }

            const onLoaded = (object) => {
                const existing = meshesById.get(scanId);
                if (existing) {
                    modelGroup.remove(existing.wrapper);
                    disposeObject(existing.wrapper);
                }

                const usesFileColors = prepareObjectMaterials(object, scanId);
                ensureMeshNormals(object);
                const wrapper = new THREE.Group();
                wrapper.name = scanId;
                wrapper.visible = true;
                wrapper.add(object);
                modelGroup.add(wrapper);
                meshesById.set(scanId, { wrapper, object, scan, usesFileColors });
                resolve();
            };

            const onError = () => reject(new Error(scan.name || scanId));

            if (ext === 'obj') {
                loadObjWithMaterials(url, onLoaded, onError);
                return;
            }

            if (ext === 'ply') {
                createPlyLoader().load(
                    url,
                    (geometry) => onLoaded(new THREE.Mesh(geometry)),
                    undefined,
                    onError
                );
                return;
            }

            new STLLoader().load(
                url,
                (geometry) => onLoaded(new THREE.Mesh(geometry)),
                undefined,
                onError
            );
        });
    }

    function setScanVisibility(scanId, isVisible) {
        const entry = meshesById.get(scanId);
        if (!entry) {
            return;
        }

        entry.wrapper.visible = isVisible;
        syncLegendVisibility(scanId, isVisible);
        syncFileCardVisibility(scanId, isVisible);

        if (viewerState.moveMode && selectedScanId === scanId && !isVisible) {
            const nextId = getDefaultMoveScanId();
            if (nextId) {
                selectScanForMove(nextId);
            } else {
                selectedScanId = null;
                syncMoveSelectionUi();
            }
        }
    }

    function bindVisibilityToggles() {
        root.querySelectorAll('.case-scan-file__action--view input[data-scan-id]').forEach((input) => {
            input.addEventListener('change', function () {
                const scanId = this.getAttribute('data-scan-id');
                if (!scanId) {
                    return;
                }

                setScanVisibility(scanId, this.checked);

                if (getVisibleMeshEntries().length) {
                    applyInitialCameraView();
                }
            });
        });
    }

    function clearAllMeshes() {
        meshesById.forEach((entry) => {
            if (entry.wrapper.parent) {
                entry.wrapper.parent.remove(entry.wrapper);
            }
            disposeObject(entry.wrapper);
        });
        meshesById.clear();
        biteGroup.rotation.set(0, 0, 0);
        biteGroup.position.set(0, 0, 0);
        selectedScanId = null;
        layoutSnapshot = null;
        syncMoveSelectionUi();
        root.querySelectorAll('.case-scan-legend__item[data-scan-id]').forEach((item) => {
            item.classList.add('is-off');
        });
    }

    function updateModificationNotes(notes, setKey) {
        if (!modNotesEl || !modNotesText) {
            return;
        }

        const text = (notes || '').trim();
        if (!text) {
            modNotesEl.classList.add('is-hidden');
            modNotesText.textContent = '';
            return;
        }

        const labelEl = document.getElementById('case-scan-mod-notes-label');
        if (labelEl && setKey) {
            if (String(setKey).startsWith('ref-')) {
                labelEl.textContent = 'Refinement notes';
            } else if (String(setKey).startsWith('mod-')) {
                labelEl.textContent = 'Modification notes';
            } else {
                labelEl.textContent = 'Case notes';
            }
        }

        modNotesText.textContent = text;
        modNotesEl.classList.remove('is-hidden');
    }

    function rebuildLegend(files) {
        const legend = root.querySelector('.case-scan-legend');
        if (!legend) {
            return;
        }

        legend.innerHTML = files.map((file) => (
            `<span class="case-scan-legend__item case-scan-legend__item--${file.id}" data-scan-id="${file.id}">`
            + `<span class="case-scan-legend__dot"></span>${file.label}</span>`
        )).join('');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function placeholderForScan(file) {
        if (file.id === 'lower') {
            return lowerPlaceholderUrl;
        }

        return upperPlaceholderUrl;
    }

    function jawLabelForScan(file) {
        if (file.label) {
            return file.label;
        }

        return file.id === 'lower' ? 'Lower 3D model' : 'Upper 3D model';
    }

    function rebuildFileList(files) {
        if (!filesListEl) {
            return;
        }

        if (filesPanelEl) {
            filesPanelEl.classList.toggle('is-hidden', !files.length);
        }

        filesListEl.innerHTML = files.map((file) => {
            const thumbUrl = placeholderForScan(file);
            const jawLabel = jawLabelForScan(file);
            const sizeLabel = file.size || file.ext || '';

            return (
                `<li class="case-scan-file-card case-scan-file-card--panel" data-scan-id="${file.id}">`
                + '<div class="case-scan-file__body">'
                + (thumbUrl
                    ? `<img class="case-scan-file__thumb" src="${escapeHtml(thumbUrl)}" alt="" width="48" height="32">`
                    : '')
                + '<div class="case-scan-file__info">'
                + `<span class="case-scan-file__jaw">${escapeHtml(jawLabel)}</span>`
                + `<span class="case-scan-file__name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>`
                + `<span class="case-scan-file__size">${escapeHtml(sizeLabel)}</span>`
                + '</div>'
                + '<div class="case-scan-file__actions">'
                + `<label class="case-scan-file__action case-scan-file__action--view" for="case-scan-vis-${file.id}" title="Show in viewer">`
                + `<input type="checkbox" id="case-scan-vis-${file.id}" checked data-scan-id="${file.id}">`
                + '<i class="zmdi zmdi-eye" aria-hidden="true"></i></label>'
                + `<a href="${escapeHtml(file.download_url)}" class="case-scan-file__action case-scan-file__action--download" download title="Download file">`
                + '<i class="zmdi zmdi-download" aria-hidden="true"></i></a>'
                + '</div></div></li>'
            );
        }).join('');

        bindMoveSelectionFromFiles();
        bindVisibilityToggles();
    }

    function findScanSet(key) {
        return scanSets.find((set) => set.key === key) || null;
    }

    async function applyScanSet(set) {
        if (!set || !Array.isArray(set.files)) {
            return;
        }

        scans = set.files;
        root.dataset.scans = JSON.stringify(scans);
        updateModificationNotes(set.notes, set.key);
        rebuildLegend(scans);
        rebuildFileList(scans);

        clearAllMeshes();

        if (!scans.length) {
            setOverlay('empty', EMPTY_SCAN_SET_MESSAGE);
            return;
        }

        await loadAll();
    }

    function getInitialScanSet() {
        const preferredKey = root.dataset.defaultScanSet || '';
        return findScanSet(preferredKey) || findScanSet(scanSetSelect?.value) || scanSets[0] || null;
    }

    function notifyScanSetChanged(key) {
        document.dispatchEvent(new CustomEvent('case-scan-set-changed', {
            detail: { key, fromViewer: true },
        }));
    }

    function bindScanSetSwitcher() {
        if (!scanSetSelect) {
            const initial = getInitialScanSet();
            if (initial) {
                updateModificationNotes(initial.notes, initial.key);
            }
            return;
        }

        scanSetSelect.addEventListener('change', () => {
            const set = findScanSet(scanSetSelect.value);
            if (set) {
                applyScanSet(set);
                notifyScanSetChanged(set.key);
            }
        });
    }

    async function loadAll() {
        if (!scans.length) {
            setOverlay('error', 'No scan files available.');
            return;
        }

        setOverlay('loading', 'Loading 3D models…');
        loadedCount = 0;

        const results = await Promise.allSettled(scans.map((scan) => loadMesh(scan)));

        loadedCount = results.filter((r) => r.status === 'fulfilled').length;

        if (loadedCount === 0) {
            setOverlay('error', 'Could not load 3D files.');
            return;
        }

        layoutMeshes();
        if (viewerState.wireframe) {
            applyWireframe();
        }
        if (viewerState.flatShading) {
            applyFlatShading();
        }
        setOverlay('none');

        afterLayout(() => {
            resize();
            applyInitialCameraView();
        });

        if (results.some((r) => r.status === 'rejected')) {
            errorText.textContent = 'Some models could not be loaded.';
            errorEl.classList.remove('is-hidden');
            setTimeout(() => errorEl.classList.add('is-hidden'), 4000);
        }
    }

    function animate() {
        if (!animating) {
            return;
        }
        requestAnimationFrame(animate);
        tickMoveSmoothing();
        controls.update();
        renderer.render(scene, camera);
    }

    const resizeObserver = new ResizeObserver(() => {
        resize();
    });
    resizeObserver.observe(canvas.parentElement);
    if (viewerPane) {
        resizeObserver.observe(viewerPane);
    }
    if (root) {
        resizeObserver.observe(root);
    }

    window.caseScanViewer = {
        resize() {
            refreshViewerLayout(true);
        },
        pause() {
            animating = false;
        },
        resume() {
            if (!animating) {
                animating = true;
                animate();
            }
        },
    };

    initToolbar();
    initModelMoveControls();
    bindMoveSelectionFromFiles();
    bindVisibilityToggles();
    const initialScanSet = getInitialScanSet();
    if (initialScanSet) {
        if (scanSetSelect) {
            scanSetSelect.value = initialScanSet.key;
        }
        scans = initialScanSet.files;
        root.dataset.scans = JSON.stringify(scans);
        updateModificationNotes(initialScanSet.notes, initialScanSet.key);
        rebuildLegend(scans);
        rebuildFileList(scans);
    }

    bindScanSetSwitcher();
    applyControlBindings();
    setGrid(true);
    setAutoRotate(false);
    syncBackground();
    applyLighting();
    resize();
    animate();

    if (initialScanSet) {
        notifyScanSetChanged(initialScanSet.key);
        if (scans.length) {
            loadAll();
        } else {
            setOverlay('empty', EMPTY_SCAN_SET_MESSAGE);
        }
    } else {
        setOverlay('empty', EMPTY_SCAN_SET_MESSAGE);
    }
}
