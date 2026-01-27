'use client';

import { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

interface RadarChartProps {
  labels: string[];
  workData: (number | null)[];
  importanceData: (number | null)[];
}

export default function RadarChart({
  labels,
  workData,
  importanceData,
}: RadarChartProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const chartRef = useRef<Chart | null>(null);

  useEffect(() => {
    if (!canvasRef.current) return;

    // 既存のチャートを破棄
    if (chartRef.current) {
      chartRef.current.destroy();
    }

    // 新しいチャートを作成
    chartRef.current = new Chart(canvasRef.current, {
      type: 'radar',
      data: {
        labels: labels,
        datasets: [
          {
            label: '満足度',
            data: workData,
            borderColor: 'rgb(107, 182, 255)',
            backgroundColor: 'rgba(107, 182, 255, 0.2)',
            pointBackgroundColor: 'rgb(107, 182, 255)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(107, 182, 255)',
          },
          {
            label: '重要度',
            data: importanceData,
            borderColor: 'rgb(139, 190, 220)',
            backgroundColor: 'rgba(139, 190, 220, 0.2)',
            pointBackgroundColor: 'rgb(139, 190, 220)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(139, 190, 220)',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          r: {
            beginAtZero: true,
            max: 100,
            ticks: {
              stepSize: 20,
            },
          },
        },
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
          },
        },
      },
    });

    // クリーンアップ
    return () => {
      if (chartRef.current) {
        chartRef.current.destroy();
      }
    };
  }, [labels, workData, importanceData]);

  return (
    <div className="bg-white rounded-xl p-6 border-2 border-[#6BB6FF]/20">
      <canvas ref={canvasRef} width={400} height={400} />
    </div>
  );
}
